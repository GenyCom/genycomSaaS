<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DetteController extends Controller
{
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

        $where = "d.deleted_at IS NULL";
        $params = [];

        if ($search) {
            $where .= " AND (d.numero LIKE ? OR f.societe LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($request->statut === 'soldee') {
            $where .= " AND d.montant_restant <= 0";
        } elseif ($request->statut === 'en_attente') {
            $where .= " AND d.montant_regle = 0 AND d.montant_restant > 0";
        } elseif ($request->statut === 'partielle') {
            $where .= " AND d.montant_regle > 0 AND d.montant_restant > 0";
        }

        $total = $this->db()->select(
            "SELECT COUNT(*) as cnt FROM dettes_fournisseur d
             LEFT JOIN fournisseurs f ON f.id = d.fournisseur_id
             WHERE {$where}",
            $params
        )[0]->cnt;

        $data = $this->db()->select(
            "SELECT d.*, f.societe AS fournisseur_societe, f.code_fournisseur,
                    br.numero AS br_numero
             FROM dettes_fournisseur d
             LEFT JOIN fournisseurs f ON f.id = d.fournisseur_id
             LEFT JOIN br br ON br.id = d.bon_reception_id
             WHERE {$where}
             ORDER BY d.id DESC
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

    public function show(Request $request, int $id): JsonResponse
    {
        $dette = $this->db()->select(
            "SELECT d.*, f.societe AS fournisseur_societe, f.code_fournisseur, f.email AS fournisseur_email,
                    br.numero AS br_numero, br.date_reception
             FROM dettes_fournisseur d
             LEFT JOIN fournisseurs f ON f.id = d.fournisseur_id
             LEFT JOIN br br ON br.id = d.bon_reception_id
             WHERE d.id = ? LIMIT 1",
            [$id]
        );

        if (empty($dette)) {
            return response()->json(['message' => 'Dette introuvable'], 404);
        }

        $reglements = $this->db()->select(
            "SELECT r.*, mr.libelle AS mode_libelle
             FROM reglements r
             LEFT JOIN mode_reglement mr ON mr.id = r.mode_reglement_id
             WHERE r.payable_type = 'App\\\\Models\\\\DetteFournisseur' AND r.payable_id = ?
             ORDER BY r.date_reglement DESC",
            [$id]
        );

        $result = $dette[0];
        $result->reglements = $reglements;

        return response()->json(['data' => $result]);
    }

    public function reglement(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'montant'           => 'required|numeric|min:0.01',
            'date_reglement'    => 'required|date',
            'mode_reglement_id' => 'nullable|integer',
            'observations'      => 'nullable|string',
        ]);

        $userId   = auth()->id();

        $this->db()->beginTransaction();
        try {
            $dette = $this->db()->select(
                "SELECT * FROM dettes_fournisseur WHERE id = ? LIMIT 1",
                [$id]
            );
            if (empty($dette)) {
                throw new \Exception("Dette introuvable");
            }
            $dette = $dette[0];

            $montant = min((float) $data['montant'], (float) $dette->montant_restant);

            // Reglement table HAS tenant_id (centralized)
            $tenantId = $request->get('current_tenant')->id;

            $this->db()->insert(
                "INSERT INTO reglements (tenant_id, payable_type, payable_id, date_reglement, montant, mode_reglement_id, observations, created_by, created_at)
                 VALUES (?, 'App\\\\Models\\\\DetteFournisseur', ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $tenantId, $id,
                    $data['date_reglement'], $montant,
                    $data['mode_reglement_id'] ?? null,
                    $data['observations'] ?? null,
                    $userId
                ]
            );

            // 1. Mise à jour de la dette
            $this->db()->update(
                "UPDATE dettes_fournisseur
                 SET montant_regle = montant_regle + ?,
                     montant_restant = montant_restant - ?,
                     updated_at = NOW()
                 WHERE id = ?",
                [$montant, $montant, $id]
            );

            // 2. SYNCHRONISATION DE LA FACTURE D'ACHAT (SI ELLE EXISTE)
            if (!empty($dette->facture_id)) {
                // On calcule en direct les nouveaux montants de la dette
                $nouveauMontantRegle = $dette->montant_regle + $montant;
                $nouveauMontantRestant = $dette->montant_restant - $montant;

                $this->db()->update(
                    "UPDATE factures_achats 
                     SET montant_paye = ?, 
                         reste_a_payer = ?,
                         statut = CASE WHEN ? <= 0 THEN 'paye' ELSE statut END,
                         updated_at = NOW()
                     WHERE id = ?",
                    [$nouveauMontantRegle, $nouveauMontantRestant, $nouveauMontantRestant, $dette->facture_id]
                );
            }

            $this->db()->commit();
            return response()->json(['message' => 'Règlement enregistré', 'montant_regle' => $montant]);
        } catch (\Throwable $e) {
            $this->db()->rollBack();
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }
}