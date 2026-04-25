<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\LigneFacture;
use App\Models\Devise;
use App\Services\FacturationService;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FactureController extends Controller
{
    public function __construct(
        private FacturationService $facturation,
        private NumerotationService $numerotation,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Facture::with(['client:id,societe,code_client', 'etat'])
            ->when($request->client_id, fn($q, $v) => $q->where('client_id', $v))
            ->when($request->etat_id, fn($q, $v) => $q->where('etat_id', $v))
            ->when($request->est_reglee !== null, fn($q) => $q->where('est_reglee', $request->boolean('est_reglee')))
            ->when($request->date_debut, fn($q, $v) => $q->where('date_facture', '>=', $v))
            ->when($request->date_fin, fn($q, $v) => $q->where('date_facture', '<=', $v))
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('numero', 'like', "%{$v}%")
                   ->orWhereHas('client', fn($cq) => $cq->where('societe', 'like', "%{$v}%"));
            }))
            ->orderBy($request->sort_by ?? 'date_facture', $request->sort_dir ?? 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'              => 'required|integer',
            'projet_id'              => 'nullable|integer',
            'date_facture'           => 'required|date',
            'date_echeance'          => 'nullable|date',
            'condition_reglement_id' => 'nullable|integer',
            'observations'           => 'nullable|string',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.produit_id'    => 'nullable|integer',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.quantite'      => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva'      => 'required|numeric|min:0',
            'lignes.*.remise_pourcent' => 'nullable|numeric|min:0|max:100',
            'lignes.*.remise_montant'  => 'nullable|numeric|min:0',
            'devise_id'               => 'nullable|integer',
            'taux_change_document'    => 'nullable|numeric',
        ]);
		$tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $userId   = auth()->id();
        $data['numero'] = $this->numerotation->generer($tenantId, 'FACTURE', new \DateTime($data['date_facture']));
        $data['tenant_id'] = $tenantId;

        // Gestion Devise & Taux
        if (empty($data['devise_id'])) {
            $devise = Devise::where('tenant_id', $tenantId)->where('is_principale', true)->first();
            $data['devise_id'] = $devise?->id;
            $data['taux_change_document'] = 1.0;
        } else {
            if (empty($data['taux_change_document'])) {
                $devise = Devise::find($data['devise_id']);
                $data['taux_change_document'] = $devise?->taux_change ?? 1.0;
            }
        }
        
        $facture = Facture::create(collect($data)->except('lignes')->toArray());

        foreach ($data['lignes'] as $index => $ligneData) {
            $ligneData['facture_id'] = $facture->id;
            $ligneData['ordre'] = $index + 1;
            $ligneData['source_type'] = 'saisie_manuelle';
            LigneFacture::create($ligneData);
        }

        $facture->recalculerTotaux();

        return response()->json($facture->fresh('lignes', 'client', 'etat'), 201);
    }

    public function show(Facture $facture): JsonResponse
    {
        return response()->json(
            $facture->load(['lignes.produit:id,reference,designation', 'client', 'etat', 'reglements.modeReglement', 'conditionReglement', 'createur:id,nom,prenom'])
        );
    }

    public function update(Request $request, Facture $facture): JsonResponse
    {
        $data = $request->validate([
            'client_id'              => 'sometimes|integer',
            'date_facture'           => 'sometimes|date',
            'date_echeance'          => 'nullable|date',
            'condition_reglement_id' => 'nullable|integer',
            'observations'           => 'nullable|string',
        ]);

        $facture->update($data);
        return response()->json($facture->fresh('lignes', 'client', 'etat'));
    }

    public function destroy(Facture $facture): JsonResponse
    {
        // Vérifier les règlements
        if ($facture->reglements()->exists() || $facture->montant_regle > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer cette facture car elle possède des règlements enregistrés. Veuillez supprimer les règlements d\'abord.'
            ], 422);
        }

        $facture->delete();
        return response()->json(['message' => 'Facture supprimée avec succès']);
    }

    /**
     * POST /api/factures/{facture}/valider
     */
    public function valider(Facture $facture): JsonResponse
    {
        $facture = $this->facturation->valider($facture);
        return response()->json($facture->load('etat'));
    }

    private function db()
    {
        return \Illuminate\Support\Facades\DB::connection('tenant');
    }
	
	/**
     * Enregistre un règlement client sur une facture.
     */
    public function reglement(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'montant'           => 'required|numeric|min:0.01',
            'date_reglement'    => 'required|date',
            'mode_reglement_id' => 'nullable|integer',
            'observations'      => 'nullable|string',
        ]);

        $userId   = auth()->id();
        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;

        $this->db()->beginTransaction();
        try {
            // 1. Récupérer la facture
            $factureRaw = $this->db()->select("SELECT * FROM factures WHERE id = ? AND tenant_id = ? LIMIT 1", [$id, $tenantId]);
            
            if (empty($factureRaw)) {
                throw new \Exception("Facture introuvable");
            }
            $facture = $factureRaw[0];

            // 2. Calculer le reste à payer
            $resteAPayer = (float) $facture->total_ttc - (float) $facture->montant_regle;
            if ($resteAPayer <= 0) {
                throw new \Exception("Cette facture est déjà totalement réglée.");
            }

            // On sécurise pour ne pas payer plus que le reste
            $montantAPayer = min((float) $data['montant'], $resteAPayer);

            // 3. Insérer le règlement dans la table centralisée (Polymorphisme sur Facture)
            $this->db()->insert(
                "INSERT INTO reglements (tenant_id, payable_type, payable_id, date_reglement, montant, mode_reglement_id, observations, created_by, created_at)
                 VALUES (?, 'App\\\\Models\\\\Facture', ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $tenantId, 
                    $id,
                    $data['date_reglement'], 
                    $montantAPayer,
                    $data['mode_reglement_id'] ?? null,
                    $data['observations'] ?? null,
                    $userId
                ]
            );

            // 4. Mettre à jour la facture
            $nouveauMontantRegle = (float) $facture->montant_regle + $montantAPayer;
            $estReglee = ($nouveauMontantRegle >= (float) $facture->total_ttc) ? 1 : 0;

            $this->db()->update(
                "UPDATE factures
                 SET montant_regle = ?,
                     est_reglee = ?,
                     updated_at = NOW()
                 WHERE id = ?",
                [$nouveauMontantRegle, $estReglee, $id]
            );

            $this->db()->commit();
            
            return response()->json([
                'message' => 'Règlement client enregistré avec succès', 
                'montant_regle' => $nouveauMontantRegle,
                'est_reglee' => $estReglee
            ]);
            
        } catch (\Throwable $e) {
            $this->db()->rollBack();
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }
}
