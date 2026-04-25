<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BR;
use App\Services\CycleAchatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BonReceptionController extends Controller
{
    public function __construct(private CycleAchatService $cycleAchat) {}

    private function db()
    {
        return DB::connection('tenant');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage  = (int) ($request->per_page ?? 20);
        $page     = (int) ($request->page ?? 1);
        $offset   = ($page - 1) * $perPage;
        $search   = $request->search;

        $where = "1=1";
        $params = [];

        if ($search) {
            $where .= " AND (br.numero LIKE ? OR f.societe LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $total = $this->db()->select(
            "SELECT COUNT(*) as cnt FROM br br
             LEFT JOIN fournisseurs f ON f.id = br.fournisseur_id
             WHERE {$where}",
            $params
        )[0]->cnt;

        $data = $this->db()->select(
            "SELECT br.*, f.societe AS fournisseur_societe, f.code_fournisseur,
                    bcf.numero AS commande_numero
             FROM br br
             LEFT JOIN fournisseurs f ON f.id = br.fournisseur_id
             LEFT JOIN bcf bcf ON bcf.id = br.bcf_id
             WHERE {$where}
             ORDER BY br.id DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return response()->json([
            'data'     => $data,
            'total'    => (int) $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $br = BR::with(['fournisseur', 'commande', 'lignes.produit'])->findOrFail($id);

        return response()->json(['data' => $br]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fournisseur_id'           => 'required|integer',
            'bcf_id'                   => 'nullable|integer',
            'date_reception'           => 'required|date',
            'observations'             => 'nullable|string',
            'lignes'                   => 'required|array|min:1',
            'lignes.*.produit_id'      => 'nullable|integer',
            'lignes.*.designation'     => 'required|string|max:255',
            'lignes.*.quantite_recue'  => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire'   => 'required|numeric|min:0',
        ]);

        $tenantId = $request->get('current_tenant')->id;
        $userId   = auth()->id();

        $this->db()->beginTransaction();
        try {
            $numero = app(\App\Services\NumerotationService::class)->generer($tenantId, 'BR');
            
            // On récupère le bcf_id ou on met null
            $bcfId = $data['bcf_id'] ?? null;
            
            $this->db()->insert(
                "INSERT INTO br (numero, date_reception, bcf_id, fournisseur_id, observations, statut, created_by, created_at)
                 VALUES (?, ?, ?, ?, ?, 'valide', ?, NOW())",
                [$numero, $data['date_reception'], $bcfId, $data['fournisseur_id'], $data['observations'] ?? null, $userId]
            );
            $brId = $this->db()->getPdo()->lastInsertId();

            foreach ($data['lignes'] as $index => $ligne) {
                $pId = (!empty($ligne['produit_id'])) ? $ligne['produit_id'] : null;
                
                $this->db()->insert(
                    "INSERT INTO br_lignes (br_id, produit_id, designation, quantite_commandee, quantite_recue, prix_unitaire, ordre)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $brId,
                        $pId, $ligne['designation'],
                        $ligne['quantite_recue'], $ligne['quantite_recue'],
                        $ligne['prix_unitaire'], $index + 1
                    ]
                );

                if ($pId) {
                    $this->cycleAchat->incrementerStock($tenantId, (int) $pId, (float) $ligne['quantite_recue'], [
                        'reference_document' => $numero,
                        'document_type'      => 'BonReception',
                        'document_id'        => $brId,
                        'prix_unitaire'      => $ligne['prix_unitaire'],
                        'user_id'            => $userId,
                    ]);
                }
            }

            $this->db()->commit();
            return response()->json(['id' => (int) $brId, 'numero' => $numero, 'message' => 'Bon de réception créé'], 201);
        } catch (\Throwable $e) {
            $this->db()->rollBack();
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    } // <---- C'EST CETTE ACCOLADE QUI MANQUAIT !

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->get('current_tenant')->id;
        $userId   = auth()->id();

        try {
            $this->cycleAchat->supprimerBr($tenantId, $id, $userId);
            return response()->json(['message' => 'Bon de réception supprimé et stock mis à jour.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}