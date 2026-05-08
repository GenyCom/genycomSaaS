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
            'projet_id'              => 'nullable|integer',
            'condition_reglement_id' => 'nullable|integer',
            'observations'           => 'nullable|string',
            'entrepot_id'            => 'nullable|integer',
            'lignes'                 => 'sometimes|array|min:1',
            'lignes.*.produit_id'    => 'nullable|integer',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.quantite'      => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva'      => 'required|numeric|min:0',
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $facture, $request) {
            $tenantId = $facture->tenant_id;
            
            // 1. Mise à jour de l'entête Facture
            $facture->update(collect($data)->except(['lignes', 'entrepot_id'])->toArray());

            // 2. Mise à jour des lignes de facture
            if (isset($data['lignes'])) {
                $facture->lignes()->delete();
                foreach ($data['lignes'] as $index => $ligneData) {
                    $ligneData['facture_id'] = $facture->id;
                    $ligneData['tenant_id']  = $tenantId;
                    $ligneData['ordre']      = $index + 1;
                    LigneFacture::create($ligneData);
                }
                $facture->recalculerTotaux();
            }

            // 3. Synchronisation avec le BL associé (si présent)
            $bl = $facture->bonLivraison;
            if ($bl) {
                // Mise à jour de l'entête BL
                $bl->update([
                    'client_id'    => $facture->client_id,
                    'projet_id'    => $facture->projet_id,
                    'total_ht'     => $facture->total_ht,
                    'total_tva'    => $facture->total_tva,
                    'total_ttc'    => $facture->total_ttc,
                    'observations' => $facture->observations,
                    'entrepot_id'  => $data['entrepot_id'] ?? $bl->entrepot_id,
                ]);

                // Mise à jour des lignes BL
                if (isset($data['lignes'])) {
                    // Annuler les anciens mouvements de stock
                    $stockService = app(\App\Services\StockService::class);
                    $stockService->annulerMouvementsDocument('BL', $bl->id, $tenantId);

                    // Recréer les lignes BL et les nouveaux mouvements
                    $bl->lignes()->delete();
                    foreach ($data['lignes'] as $index => $ligneData) {
                        $lbl = \App\Models\LigneBonLivraison::create([
                            'tenant_id'        => $tenantId,
                            'bon_livraison_id' => $bl->id,
                            'produit_id'       => $ligneData['produit_id'] ?? null,
                            'designation'      => $ligneData['designation'],
                            'quantite_prevue'  => $ligneData['quantite'],
                            'quantite_livree'  => $ligneData['quantite'],
                            'prix_unitaire'    => $ligneData['prix_unitaire'],
                            'taux_tva'         => $ligneData['taux_tva'],
                            'montant_ht'       => ($ligneData['quantite'] * $ligneData['prix_unitaire']),
                            'montant_tva'      => ($ligneData['quantite'] * $ligneData['prix_unitaire'] * ($ligneData['taux_tva'] / 100)),
                            'montant_ttc'      => ($ligneData['quantite'] * $ligneData['prix_unitaire'] * (1 + $ligneData['taux_tva'] / 100)),
                            'ordre'            => $index + 1,
                        ]);

                        // Nouveau mouvement de stock
                        if ($lbl->produit_id) {
                            $stockService->enregistrerMouvement(
                                $lbl->produit_id,
                                $lbl->quantite_livree,
                                'sortie_vente',
                                'BL',
                                $bl->id,
                                auth()->id(),
                                $tenantId,
                                $bl->entrepot_id
                            );
                        }
                    }
                }
            }

            return response()->json($facture->fresh(['lignes', 'client', 'etat', 'bonLivraison']));
        });
    }

    public function destroy(Facture $facture): JsonResponse
    {
        // Vérifier les  règlements
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
            'montant'           => 'required|numeric',
            'date_reglement'    => 'required|date',
            'mode_reglement_id' => 'nullable|integer',
            'observations'      => 'nullable|string',
        ]);

        $userId   = auth()->id();
        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $facture = Facture::where('tenant_id', $tenantId)->findOrFail($id);

            // On autorise désormais tout montant (positif ou négatif) même si la facture est soldée, 
            // pour permettre les corrections manuelles suite à un changement de montant de facture.
            $montantAPayer = (float) $data['montant'];

            $facture->reglements()->create([
                'tenant_id'         => $tenantId,
                'date_reglement'    => $data['date_reglement'],
                'montant'           => $montantAPayer,
                'mode_reglement_id' => $data['mode_reglement_id'] ?? null,
                'observations'      => $data['observations'] ?? null,
                'created_by'        => $userId
            ]);

            $facture->enregistrerReglement($montantAPayer);

            \Illuminate\Support\Facades\DB::commit();
            
            return response()->json([
                'message' => 'Règlement client enregistré avec succès', 
                'montant_regle' => $facture->montant_regle,
                'est_reglee' => $facture->est_reglee
            ]);
            
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \App\Exceptions\ExceptionMailReporter::report($e);
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    /**
     * Annule une facture et ses conséquences (règlements, stock du BL lié).
     */
    public function annuler(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;

        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $tenantId) {
            $facture = Facture::where('tenant_id', $tenantId)->findOrFail($id);
            
            // 1. Trouver ou créer l'état Annulé
            $etatAnnule = \App\Models\EtatDocument::firstOrCreate(
                ['tenant_id' => $tenantId, 'type_document' => 'facture', 'code' => 'ANN'],
                ['libelle' => 'Annulée', 'couleur' => '#EF4444', 'is_system' => true]
            );

            // 2. Annuler les règlements (Suppression pour rétablir les encours)
            $facture->reglements()->delete();
            $facture->montant_regle = 0;
            $facture->montant_restant = $facture->total_ttc;
            $facture->est_reglee = false;

            // 3. Si un BL est lié, on annule les mouvements de stock
            $bl = $facture->bonLivraison;
            if ($bl) {
                $stockService = app(\App\Services\StockService::class);
                $stockService->annulerMouvementsDocument('BL', $bl->id, $tenantId);
                
                $etatAnnuleBl = \App\Models\EtatDocument::firstOrCreate(
                    ['tenant_id' => $tenantId, 'type_document' => 'bl', 'code' => 'ANN'],
                    ['libelle' => 'Annulé', 'couleur' => '#EF4444', 'is_system' => true]
                );
                $bl->update(['etat_id' => $etatAnnuleBl->id, 'statut' => 'annule']);
            }

            // 4. Mettre à jour la facture
            $facture->update(['etat_id' => $etatAnnule->id]);
            
            // 5. Recalculer encours client
            if ($facture->client) {
                $facture->client->recalculerEncours();
            }

            return response()->json([
                'message' => 'Facture annulée avec succès. Règlements supprimés et stocks restaurés.',
                'facture' => $facture->load('etat', 'bonLivraison')
            ]);
        });
    }
}
