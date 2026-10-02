<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\LigneFacture;
use App\Models\Devise;
use App\Models\Client;
use App\Models\Produit;
use App\Models\EtatDocument;
use App\Models\ModeReglement;
use App\Models\PosCloture;
use App\Services\NumerotationService;
use App\Services\StockService;
use App\Services\PosClotureService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PosController extends Controller
{
    public function __construct(
        private NumerotationService $numerotation,
        private PosClotureService $clotureService,
    ) {}

    /**
     * GET /api/pos/products
     * Returns all active products for POS product grid.
     */
    public function products(Request $request): JsonResponse
    {
        $query = Produit::select([
                'id', 'reference', 'code_barre', 'designation', 'prix_ttc_vente',
                'prix_ht_vente', 'taux_tva', 'stock_actuel', 'is_service',
                'famille_id', 'image_path', 'unite',
            ])
            ->where('is_actif', true)
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('designation', 'like', "%{$v}%")
                   ->orWhere('reference', 'like', "%{$v}%")
                   ->orWhere('code_barre', 'like', "%{$v}%");
            }))
            ->when($request->famille_id, fn($q, $v) => $q->where('famille_id', $v))
            ->with('famille:id,libelle')
            ->orderBy('designation')
            ->limit(200);

        return response()->json($query->get());
    }

    /**
     * GET /api/pos/product-by-barcode/{barcode}
     * Find a product by barcode for scanner input.
     */
    public function productByBarcode(string $barcode): JsonResponse
    {
        $product = Produit::select([
                'id', 'reference', 'code_barre', 'designation', 'prix_ttc_vente',
                'prix_ht_vente', 'taux_tva', 'stock_actuel', 'is_service',
                'famille_id', 'image_path', 'unite',
            ])
            ->where('is_actif', true)
            ->where('code_barre', $barcode)
            ->with('famille:id,libelle')
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé pour ce code-barres.'], 404);
        }

        return response()->json($product);
    }

    /**
     * GET /api/pos/config
     * Returns POS configuration (devise symbol, etc).
     */
    public function config(Request $request): JsonResponse
    {
        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $devise = Devise::where('tenant_id', $tenantId)->where('is_principale', true)->first();
        return response()->json([
            'devise_symbole' => $devise->symbole ?? $devise->code_iso ?? 'MAD',
            'devise_code'    => $devise->code_iso ?? 'MAD',
        ]);
    }

    /**
     * GET /api/pos/payment-modes
     * Returns available payment modes for the tenant.
     */
    public function paymentModes(Request $request): JsonResponse
    {
        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $modes = ModeReglement::where('tenant_id', $tenantId)->orderBy('libelle')->get();
        return response()->json($modes);
    }

    /**
     * GET /api/pos/history
     * Returns POS counter sales with date/period filters (default: today).
     */
    public function history(Request $request): JsonResponse
    {
        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Facture::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('observations', 'like', '[POS]%')
                  ->orWhereHas('client', function ($cq) {
                      $cq->where('nom', 'like', '%Comptoir%');
                  });
            });

        if ($startDate && $endDate) {
            $from = Carbon::parse($startDate)->startOfDay();
            $to = Carbon::parse($endDate)->endOfDay();
            $query->where(function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to])
                  ->orWhere(function ($sub) use ($from, $to) {
                      $sub->whereNull('created_at')
                          ->whereBetween('date_facture', [$from->toDateString(), $to->toDateString()]);
                  });
            });
        } elseif ($period === 'today') {
            $today = Carbon::today();
            $query->where(function ($q) use ($today) {
                $q->whereDate('created_at', $today)
                  ->orWhere(function ($sub) use ($today) {
                      $sub->whereNull('created_at')
                          ->whereDate('date_facture', $today);
                  });
            });
        } elseif ($period === 'week' || $period === '7days') {
            $from = Carbon::now()->subDays(7)->startOfDay();
            $query->where(function ($q) use ($from) {
                $q->where('created_at', '>=', $from)
                  ->orWhere(function ($sub) use ($from) {
                      $sub->whereNull('created_at')
                          ->where('date_facture', '>=', $from->toDateString());
                  });
            });
        } elseif ($period === 'month' || $period === '30days') {
            $from = Carbon::now()->subDays(30)->startOfDay();
            $query->where(function ($q) use ($from) {
                $q->where('created_at', '>=', $from)
                  ->orWhere(function ($sub) use ($from) {
                      $sub->whereNull('created_at')
                          ->where('date_facture', '>=', $from->toDateString());
                  });
            });
        } elseif ($period === '3months' || $period === '90days') {
            $from = Carbon::now()->subMonths(3)->startOfDay();
            $query->where(function ($q) use ($from) {
                $q->where('created_at', '>=', $from)
                  ->orWhere(function ($sub) use ($from) {
                      $sub->whereNull('created_at')
                          ->where('date_facture', '>=', $from->toDateString());
                  });
            });
        } elseif ($period === 'all') {
            // Pas de restriction temporelle
        } else {
            // Par défaut: aujourd'hui
            $today = Carbon::today();
            $query->where(function ($q) use ($today) {
                $q->whereDate('created_at', $today)
                  ->orWhere(function ($sub) use ($today) {
                      $sub->whereNull('created_at')
                          ->whereDate('date_facture', $today);
                  });
            });
        }

        $sales = $query
            ->with([
                'lignes:id,facture_id,quantite',
                'reglements.modeReglement:id,libelle',
                'etat:id,code,libelle,couleur',
                'createur:id,nom,prenom'
            ])
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(function ($f) {
                $reglement = $f->reglements->first();
                $mode = $reglement?->modeReglement?->libelle ?? 'Espèces';
                if ($reglement && !$reglement->modeReglement && $reglement->observations) {
                    $mode = str_replace('Paiement POS — ', '', $reglement->observations);
                }

                $estAnnulee = ($f->etat?->code === 'ANN');

                return [
                    'id'             => $f->id,
                    'numero'         => $f->numero,
                    'date'           => $f->date_facture ? $f->date_facture->format('d/m/Y') : null,
                    'heure'          => $f->created_at ? $f->created_at->format('H:i') : null,
                    'datetime'       => $f->created_at ? $f->created_at->format('d/m/Y H:i') : ($f->date_facture ? $f->date_facture->format('d/m/Y') : ''),
                    'total_ttc'      => (float) $f->total_ttc,
                    'total_ht'       => (float) $f->total_ht,
                    'total_tva'      => (float) $f->total_tva,
                    'nb_articles'    => (int) $f->lignes->sum('quantite'),
                    'mode_paiement'  => ucfirst($mode),
                    'est_reglee'     => (bool) $f->est_reglee,
                    'est_annulee'    => $estAnnulee,
                    'etat'           => [
                        'code'    => $f->etat?->code ?? ($f->est_reglee ? 'PAY' : 'VAL'),
                        'libelle' => $f->etat?->libelle ?? ($f->est_reglee ? 'Payée' : 'Validée'),
                        'couleur' => $f->etat?->couleur ?? ($f->est_reglee ? '#10B981' : '#3B82F6'),
                    ],
                    'observations'   => $f->observations,
                    'caissier'       => $f->createur ? trim(($f->createur->prenom ?? '') . ' ' . ($f->createur->nom ?? '')) : 'Caissier',
                ];
            });

        return response()->json($sales);
    }

    /**
     * POST /api/pos/checkout
     * Fast-Track POS checkout:
     * Executes in 1 single Database Transaction:
     * 1. Creates a Facture (or ticket) validated directly for "Client Comptoir".
     * 2. Marks document as "Payé" immediately (records full payment).
     * 3. Decrements stock for sold products.
     */
    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'              => 'nullable|integer',
            'entrepot_id'            => 'nullable|integer',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.produit_id'    => 'nullable|integer',
            'lignes.*.produit_fini_id' => 'nullable|integer',
            'lignes.*.is_produit_fini' => 'nullable|boolean',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.quantite'      => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva'      => 'required|numeric|min:0',
            'lignes.*.remise_pourcent' => 'nullable|numeric|min:0|max:100',
            'lignes.*.remise_montant'  => 'nullable|numeric|min:0',
            'mode_paiement'          => 'required|string|in:especes,carte,cheque,virement,mixte',
            'mode_reglement_id'      => 'nullable|integer',
            'montant_recu'           => 'nullable|numeric|min:0',
            'observations'           => 'nullable|string',
        ]);

        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $userId   = auth()->id();

        try {
            return DB::transaction(function () use ($data, $tenantId, $userId) {
                $now = now();

                // 1. Get or create "Client Comptoir" (walk-in customer)
                $clientId = $data['client_id'] ?? null;
                if (!$clientId) {
                    $clientId = $this->getOrCreateClientComptoir($tenantId);
                }

                // 2. Get default devise
                $devise = Devise::where('tenant_id', $tenantId)->where('is_principale', true)->first();

                // 3. Generate Invoice Number & retrieve Payé/Validé states
                $numero = $this->numerotation->generer($tenantId, 'FACTURE', $now);

                $etatPaye = EtatDocument::where('tenant_id', $tenantId)
                    ->where('type_document', 'facture')
                    ->where('code', 'PAY')
                    ->first();

                $etatValide = EtatDocument::where('tenant_id', $tenantId)
                    ->where('type_document', 'facture')
                    ->where('code', 'VAL')
                    ->first();

                // 4. Create Facture Header
                $facture = Facture::create([
                    'tenant_id'             => $tenantId,
                    'numero'                => $numero,
                    'date_facture'          => $now->toDateString(),
                    'date_echeance'         => $now->toDateString(),
                    'client_id'             => $clientId,
                    'total_ht'              => 0,
                    'total_tva'             => 0,
                    'total_ttc'             => 0,
                    'total_remise'          => 0,
                    'montant_regle'         => 0,
                    'montant_restant'       => 0,
                    'est_reglee'            => false,
                    'etat_id'              => $etatPaye?->id ?? $etatValide?->id,
                    'devise_id'             => $devise?->id,
                    'taux_change_document'  => 1.0,
                    'observations'          => '[POS] ' . ($data['observations'] ?? 'Vente au comptoir'),
                    'created_by'            => $userId,
                ]);

                // 5. Create Line Items
                foreach ($data['lignes'] as $index => $ligneData) {
                    LigneFacture::create([
                        'tenant_id'       => $tenantId,
                        'facture_id'      => $facture->id,
                        'produit_id'      => $ligneData['produit_id'] ?? null,
                        'produit_fini_id' => $ligneData['produit_fini_id'] ?? null,
                        'is_produit_fini' => $ligneData['is_produit_fini'] ?? false,
                        'designation'     => $ligneData['designation'],
                        'quantite'        => $ligneData['quantite'],
                        'prix_unitaire'   => $ligneData['prix_unitaire'],
                        'taux_tva'        => $ligneData['taux_tva'],
                        'remise_pourcent' => $ligneData['remise_pourcent'] ?? 0,
                        'remise_montant'  => $ligneData['remise_montant'] ?? 0,
                        'ordre'           => $index + 1,
                        'source_type'     => 'pos',
                    ]);
                }

                // 6. Recalculate totals
                $facture->recalculerTotaux();
                $facture->refresh();

                // 7. Resolve Mode de Règlement
                $modeReglementId = $data['mode_reglement_id'] ?? null;
                if (!$modeReglementId) {
                    $modeLabel = match ($data['mode_paiement']) {
                        'carte'    => 'carte',
                        'cheque'   => 'chèque',
                        'virement' => 'virement',
                        default    => 'espèces',
                    };
                    $mode = ModeReglement::where('tenant_id', $tenantId)
                        ->where('libelle', 'like', "%{$modeLabel}%")
                        ->first();
                    $modeReglementId = $mode?->id;
                }

                // 8. Record Payment morph relation & mark document as Paid
                $facture->reglements()->create([
                    'tenant_id'         => $tenantId,
                    'date_reglement'    => $now->toDateString(),
                    'montant'           => $facture->total_ttc,
                    'mode_reglement_id' => $modeReglementId,
                    'observations'      => 'Paiement POS — ' . $data['mode_paiement'],
                    'created_by'        => $userId,
                ]);

                $facture->enregistrerReglement($facture->total_ttc);

                // 9. Decrement stock for sold products
                $stockService = app(StockService::class);
                $entrepotId   = $data['entrepot_id'] ?? $stockService->getDefaultEntrepotId($tenantId);

                foreach ($facture->lignes as $ligne) {
                    if ($ligne->is_produit_fini && $ligne->produit_fini_id) {
                        $produitFini = \App\Models\ProduitFini::with('nomenclature.produit')->find($ligne->produit_fini_id);
                        if ($produitFini) {
                            foreach ($produitFini->nomenclature as $nomItem) {
                                $comp = $nomItem->produit;
                                if ($comp && !$comp->is_service) {
                                    $qtyToMvt = (float)$ligne->quantite * (float)$nomItem->quantite;
                                    $stockService->enregistrerMouvement(
                                        $comp->id,
                                        $qtyToMvt,
                                        'sortie_vente',
                                        'FACTURE',
                                        $facture->id,
                                        $userId,
                                        $tenantId,
                                        $entrepotId
                                    );
                                }
                            }
                        }
                    } elseif ($ligne->produit_id) {
                        $produit = Produit::find($ligne->produit_id);
                        if ($produit && !$produit->is_service) {
                            $stockService->enregistrerMouvement(
                                $ligne->produit_id,
                                (float)$ligne->quantite,
                                'sortie_vente',
                                'FACTURE',
                                $facture->id,
                                $userId,
                                $tenantId,
                                $entrepotId
                            );
                        }
                    }
                }

                // 10. Calculate change return
                $montantRecu = $data['montant_recu'] ?? $facture->total_ttc;
                $monnaie     = max(0, $montantRecu - $facture->total_ttc);

                return response()->json([
                    'success'      => true,
                    'message'      => 'Vente POS enregistrée et facturée avec succès',
                    'facture'      => $facture->fresh(['lignes', 'client', 'etat', 'reglements']),
                    'numero'       => $numero,
                    'total_ttc'    => $facture->total_ttc,
                    'montant_recu' => $montantRecu,
                    'monnaie'      => round($monnaie, 2),
                ], 201);
            });
        } catch (\Throwable $e) {
            \Log::error('POS Checkout failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Erreur lors du checkout POS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/pos/sales/{id}
     * Returns full detail of a POS sale.
     */
    public function saleDetail(Request $request, $id): JsonResponse
    {
        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $rawId = is_numeric($id) ? (int)$id : (\App\Services\IdEncoder::decode($id) ?? $id);
        $facture = Facture::where('tenant_id', $tenantId)
            ->with([
                'lignes.produit:id,reference,code_barre,designation,prix_ttc_vente,prix_ht_vente,taux_tva,stock_actuel,is_service',
                'client:id,nom,societe,code_client,telephone',
                'reglements.modeReglement:id,libelle',
                'etat:id,code,libelle,couleur',
                'createur:id,nom,prenom,email',
            ])
            ->findOrFail($rawId);

        $reglement = $facture->reglements->first();
        $mode = $reglement?->modeReglement?->libelle ?? 'Espèces';
        if ($reglement && !$reglement->modeReglement && $reglement->observations) {
            $mode = str_replace('Paiement POS — ', '', $reglement->observations);
        }

        $estAnnulee = ($facture->etat?->code === 'ANN');

        return response()->json([
            'id'                 => $facture->id,
            'numero'             => $facture->numero,
            'date_facture'       => $facture->date_facture ? $facture->date_facture->format('d/m/Y') : '',
            'heure'              => $facture->created_at ? $facture->created_at->format('H:i') : '',
            'datetime'           => $facture->created_at ? $facture->created_at->format('d/m/Y H:i') : ($facture->date_facture ? $facture->date_facture->format('d/m/Y') : ''),
            'total_ht'           => (float) $facture->total_ht,
            'total_tva'          => (float) $facture->total_tva,
            'total_ttc'          => (float) $facture->total_ttc,
            'total_remise'       => (float) $facture->total_remise,
            'montant_regle'      => (float) $facture->montant_regle,
            'est_reglee'         => (bool) $facture->est_reglee,
            'est_annulee'        => $estAnnulee,
            'etat'               => [
                'code'    => $facture->etat?->code ?? ($facture->est_reglee ? 'PAY' : 'VAL'),
                'libelle' => $facture->etat?->libelle ?? ($facture->est_reglee ? 'Payée' : 'Validée'),
                'couleur' => $facture->etat?->couleur ?? ($facture->est_reglee ? '#10B981' : '#3B82F6'),
            ],
            'mode_paiement'      => ucfirst($mode),
            'mode_paiement_code' => str_contains(strtolower($mode), 'cart') ? 'carte' : (str_contains(strtolower($mode), 'ch') ? 'cheque' : (str_contains(strtolower($mode), 'vir') ? 'virement' : 'especes')),
            'mode_reglement_id'  => $reglement?->mode_reglement_id,
            'observations'       => $facture->observations,
            'client'             => $facture->client,
            'caissier'           => $facture->createur ? trim(($facture->createur->prenom ?? '') . ' ' . ($facture->createur->nom ?? '')) : 'Caissier',
            'lignes'             => $facture->lignes->map(function ($l) {
                return [
                    'id'              => $l->id,
                    'produit_id'      => $l->produit_id,
                    'produit_fini_id' => $l->produit_fini_id,
                    'is_produit_fini' => (bool) $l->is_produit_fini,
                    'designation'     => $l->designation,
                    'reference'       => $l->produit?->reference ?? '',
                    'code_barre'      => $l->produit?->code_barre ?? '',
                    'quantite'        => (float) $l->quantite,
                    'prix_unitaire'   => (float) $l->prix_unitaire,
                    'taux_tva'        => (float) $l->taux_tva,
                    'remise_pourcent' => (float) $l->remise_pourcent,
                    'remise_montant'  => (float) $l->remise_montant,
                    'total_ht'        => (float) $l->montant_ht,
                    'total_ttc'       => (float) $l->montant_ttc,
                ];
            }),
        ]);
    }

    /**
     * POST /api/pos/sales/{id}/cancel
     * Cancels a POS sale:
     * 1. Checks not already cancelled.
     * 2. Restores physical stock.
     * 3. Deletes payments so drawer & Z-report are deducted.
     * 4. Updates etat to 'ANN'.
     * 5. Annotates observations with user, motif, timestamp.
     */
    public function cancelSale(Request $request, $id): JsonResponse
    {
        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $userId   = auth()->id();
        $motif    = $request->input('motif', 'Annulation demandée au Point de Vente');
        $rawId    = is_numeric($id) ? (int)$id : (\App\Services\IdEncoder::decode($id) ?? $id);

        return DB::transaction(function () use ($rawId, $tenantId, $userId, $motif) {
            $facture = Facture::where('tenant_id', $tenantId)
                ->with(['lignes', 'etat', 'reglements'])
                ->findOrFail($rawId);

            if ($facture->etat?->code === 'ANN') {
                return response()->json(['message' => 'Cette vente a déjà été annulée.'], 422);
            }

            // 1. Enregistrer des mouvements de retour ('entree_retour') pour réintégrer le stock
            $stockService = app(StockService::class);
            $entrepotId   = $stockService->getDefaultEntrepotId($tenantId);
            $affectedProductIds = [];

            foreach ($facture->lignes as $ligne) {
                if ($ligne->is_produit_fini && $ligne->produit_fini_id) {
                    $produitFini = \App\Models\ProduitFini::with('nomenclature.produit')->find($ligne->produit_fini_id);
                    if ($produitFini) {
                        foreach ($produitFini->nomenclature as $nomItem) {
                            $comp = $nomItem->produit;
                            if ($comp && !$comp->is_service) {
                                $qtyToMvt = (float)$ligne->quantite * (float)$nomItem->quantite;
                                $stockService->enregistrerMouvement(
                                    $comp->id,
                                    $qtyToMvt,
                                    'entree_retour',
                                    'FACTURE',
                                    $facture->id,
                                    $userId,
                                    $tenantId,
                                    $entrepotId
                                );
                                $affectedProductIds[] = $comp->id;
                            }
                        }
                    }
                } elseif ($ligne->produit_id) {
                    $produit = Produit::find($ligne->produit_id);
                    if ($produit && !$produit->is_service) {
                        $stockService->enregistrerMouvement(
                            $ligne->produit_id,
                            (float)$ligne->quantite,
                            'entree_retour',
                            'FACTURE',
                            $facture->id,
                            $userId,
                            $tenantId,
                            $entrepotId
                        );
                        $affectedProductIds[] = $ligne->produit_id;
                    }
                }
            }

            // Recalculer le stock_actuel de tous les produits concernés
            $uniqueProductIds = array_unique(array_filter($affectedProductIds));
            foreach ($uniqueProductIds as $pId) {
                $totalStock = \App\Models\Stock::where('tenant_id', $tenantId)
                    ->where('produit_id', $pId)
                    ->sum('quantite');
                Produit::where('id', $pId)->where('tenant_id', $tenantId)->update(['stock_actuel' => $totalStock]);
            }

            // 2. Annuler les règlements
            $facture->reglements()->delete();
            $facture->montant_regle = 0;
            $facture->montant_restant = $facture->total_ttc;
            $facture->est_reglee = false;

            // 3. Mettre l'état à ANN
            $etatAnnule = EtatDocument::firstOrCreate(
                ['tenant_id' => $tenantId, 'type_document' => 'facture', 'code' => 'ANN'],
                ['libelle' => 'Annulée', 'couleur' => '#EF4444', 'is_system' => true]
            );
            $facture->etat_id = $etatAnnule->id;

            // 4. Mettre à jour les observations
            $userName = auth()->user() ? trim((auth()->user()->prenom ?? '') . ' ' . (auth()->user()->nom ?? '')) : 'Caissier';
            $timestamp = Carbon::now()->format('d/m/Y H:i');
            $cancellationNote = "\n[ANNULÉE le {$timestamp} par {$userName} - Motif: {$motif}]";
            $facture->observations = ($facture->observations ?? '') . $cancellationNote;

            $facture->save();

            if ($facture->client) {
                $facture->client->recalculerEncours();
            }

            return response()->json([
                'success' => true,
                'message' => "La vente #{$facture->numero} a été annulée avec succès. Un mouvement de retour en stock (+{$facture->lignes->sum('quantite')}) a été enregistré.",
                'facture' => $facture->fresh(['etat', 'lignes']),
            ]);
        });
    }

    /**
     * PUT /api/pos/sales/{id}/rectify
     * Rectifies/modifies a POS sale:
     * 1. Reverses previous stock movements via 'entree_retour'.
     * 2. Replaces invoice lines.
     * 3. Re-records stock movements via 'sortie_vente'.
     * 4. Updates totals and payment.
     */
    public function rectifySale(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'lignes'                   => 'required|array|min:1',
            'lignes.*.produit_id'      => 'nullable|integer',
            'lignes.*.produit_fini_id' => 'nullable|integer',
            'lignes.*.is_produit_fini' => 'nullable|boolean',
            'lignes.*.designation'     => 'required|string|max:255',
            'lignes.*.quantite'        => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire'   => 'required|numeric|min:0',
            'lignes.*.taux_tva'        => 'required|numeric|min:0',
            'lignes.*.remise_pourcent' => 'nullable|numeric|min:0|max:100',
            'lignes.*.remise_montant'  => 'nullable|numeric|min:0',
            'mode_paiement'            => 'required|string|in:especes,carte,cheque,virement,mixte',
            'mode_reglement_id'        => 'nullable|integer',
            'montant_recu'             => 'nullable|numeric|min:0',
            'motif_rectification'      => 'nullable|string|max:255',
        ]);

        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $userId   = auth()->id();
        $rawId    = is_numeric($id) ? (int)$id : (\App\Services\IdEncoder::decode($id) ?? $id);

        return DB::transaction(function () use ($rawId, $data, $tenantId, $userId) {
            $facture = Facture::where('tenant_id', $tenantId)
                ->with(['lignes', 'etat', 'reglements'])
                ->findOrFail($rawId);

            if ($facture->etat?->code === 'ANN') {
                return response()->json(['message' => 'Impossible de rectifier une vente déjà annulée.'], 422);
            }

            $stockService = app(StockService::class);
            $entrepotId   = $stockService->getDefaultEntrepotId($tenantId);

            $affectedProductIds = [];

            // 1. Inverser les mouvements de stock des anciennes lignes via 'entree_retour'
            foreach ($facture->lignes as $oldLigne) {
                if ($oldLigne->is_produit_fini && $oldLigne->produit_fini_id) {
                    $produitFini = \App\Models\ProduitFini::with('nomenclature.produit')->find($oldLigne->produit_fini_id);
                    if ($produitFini) {
                        foreach ($produitFini->nomenclature as $nomItem) {
                            $comp = $nomItem->produit;
                            if ($comp && !$comp->is_service) {
                                $qtyToMvt = (float)$oldLigne->quantite * (float)$nomItem->quantite;
                                $stockService->enregistrerMouvement(
                                    $comp->id,
                                    $qtyToMvt,
                                    'entree_retour',
                                    'FACTURE',
                                    $facture->id,
                                    $userId,
                                    $tenantId,
                                    $entrepotId
                                );
                                $affectedProductIds[] = $comp->id;
                            }
                        }
                    }
                } elseif ($oldLigne->produit_id) {
                    $produit = Produit::find($oldLigne->produit_id);
                    if ($produit && !$produit->is_service) {
                        $stockService->enregistrerMouvement(
                            $oldLigne->produit_id,
                            (float)$oldLigne->quantite,
                            'entree_retour',
                            'FACTURE',
                            $facture->id,
                            $userId,
                            $tenantId,
                            $entrepotId
                        );
                        $affectedProductIds[] = $oldLigne->produit_id;
                    }
                }
            }

            // 2. Supprimer les anciennes lignes
            $facture->lignes()->delete();

            // 3. Insérer les nouvelles lignes
            foreach ($data['lignes'] as $index => $ligneData) {
                LigneFacture::create([
                    'tenant_id'       => $tenantId,
                    'facture_id'      => $facture->id,
                    'produit_id'      => $ligneData['produit_id'] ?? null,
                    'produit_fini_id' => $ligneData['produit_fini_id'] ?? null,
                    'is_produit_fini' => $ligneData['is_produit_fini'] ?? false,
                    'designation'     => $ligneData['designation'],
                    'quantite'        => $ligneData['quantite'],
                    'prix_unitaire'   => $ligneData['prix_unitaire'],
                    'taux_tva'        => $ligneData['taux_tva'],
                    'remise_pourcent' => $ligneData['remise_pourcent'] ?? 0,
                    'remise_montant'  => $ligneData['remise_montant'] ?? 0,
                    'ordre'           => $index + 1,
                    'source_type'     => 'pos',
                ]);
            }

            // 4. Recalculer les totaux de la facture
            $facture->recalculerTotaux();
            $facture->refresh();

            // 5. Enregistrer les nouveaux mouvements de stock ('sortie_vente')
            foreach ($facture->lignes as $ligne) {
                if ($ligne->is_produit_fini && $ligne->produit_fini_id) {
                    $produitFini = \App\Models\ProduitFini::with('nomenclature.produit')->find($ligne->produit_fini_id);
                    if ($produitFini) {
                        foreach ($produitFini->nomenclature as $nomItem) {
                            $comp = $nomItem->produit;
                            if ($comp && !$comp->is_service) {
                                $qtyToMvt = (float)$ligne->quantite * (float)$nomItem->quantite;
                                $stockService->enregistrerMouvement(
                                    $comp->id,
                                    $qtyToMvt,
                                    'sortie_vente',
                                    'FACTURE',
                                    $facture->id,
                                    $userId,
                                    $tenantId,
                                    $entrepotId
                                );
                                $affectedProductIds[] = $comp->id;
                            }
                        }
                    }
                } elseif ($ligne->produit_id) {
                    $produit = Produit::find($ligne->produit_id);
                    if ($produit && !$produit->is_service) {
                        $stockService->enregistrerMouvement(
                            $ligne->produit_id,
                            (float)$ligne->quantite,
                            'sortie_vente',
                            'FACTURE',
                            $facture->id,
                            $userId,
                            $tenantId,
                            $entrepotId
                        );
                        $affectedProductIds[] = $ligne->produit_id;
                    }
                }
            }

            // 6. Recalculer stock_actuel pour tous les produits impactés
            $uniqueProductIds = array_unique(array_filter($affectedProductIds));
            foreach ($uniqueProductIds as $pId) {
                $totalStock = \App\Models\Stock::where('tenant_id', $tenantId)
                    ->where('produit_id', $pId)
                    ->sum('quantite');
                Produit::where('id', $pId)->where('tenant_id', $tenantId)->update(['stock_actuel' => $totalStock]);
            }

            // 7. Mettre à jour le règlement
            $modeReglementId = $data['mode_reglement_id'] ?? null;
            if (!$modeReglementId) {
                $modeLabel = match ($data['mode_paiement']) {
                    'carte'    => 'carte',
                    'cheque'   => 'chèque',
                    'virement' => 'virement',
                    default    => 'espèces',
                };
                $mode = ModeReglement::where('tenant_id', $tenantId)
                    ->where('libelle', 'like', "%{$modeLabel}%")
                    ->first();
                $modeReglementId = $mode?->id;
            }

            $facture->reglements()->delete();
            $facture->reglements()->create([
                'tenant_id'         => $tenantId,
                'date_reglement'    => now()->toDateString(),
                'montant'           => $facture->total_ttc,
                'mode_reglement_id' => $modeReglementId,
                'observations'      => 'Paiement POS (Rectifié) — ' . $data['mode_paiement'],
                'created_by'        => $userId,
            ]);

            $etatPaye = EtatDocument::where('tenant_id', $tenantId)
                ->where('type_document', 'facture')
                ->where('code', 'PAY')
                ->first();
            if ($etatPaye) {
                $facture->etat_id = $etatPaye->id;
            }
            $facture->montant_regle = $facture->total_ttc;
            $facture->montant_restant = 0;
            $facture->est_reglee = true;

            // 8. Annoter dans observations
            $userName = auth()->user() ? trim((auth()->user()->prenom ?? '') . ' ' . (auth()->user()->nom ?? '')) : 'Caissier';
            $timestamp = Carbon::now()->format('d/m/Y H:i');
            $motif = $data['motif_rectification'] ?? 'Rectification effectuée en caisse';
            $rectifyNote = "\n[RECTIFIÉE le {$timestamp} par {$userName} - Motif: {$motif}]";
            $facture->observations = ($facture->observations ?? '') . $rectifyNote;

            $facture->save();

            $montantRecu = $data['montant_recu'] ?? $facture->total_ttc;
            $monnaie     = max(0, $montantRecu - $facture->total_ttc);

            return response()->json([
                'success'      => true,
                'message'      => "La vente #{$facture->numero} a été rectifiée avec succès.",
                'facture'      => $facture->fresh(['lignes.produit', 'client', 'etat', 'reglements']),
                'numero'       => $facture->numero,
                'total_ttc'    => $facture->total_ttc,
                'montant_recu' => $montantRecu,
                'monnaie'      => round($monnaie, 2),
            ]);
        });
    }

    /**
     * Get or create the walk-in customer "Client Comptoir".
     */
    private function getOrCreateClientComptoir(int $tenantId): int
    {
        $client = Client::where('tenant_id', $tenantId)
            ->where('code_client', 'COMPTOIR')
            ->first();

        if (!$client) {
            $client = Client::create([
                'tenant_id'   => $tenantId,
                'code_client' => 'COMPTOIR',
                'societe'     => 'Client Comptoir',
                'type'        => 'particulier',
                'email'       => null,
                'telephone'   => null,
                'adresse'     => null,
                'ville'       => null,
            ]);
        }

        return $client->id;
    }

    /**
     * GET /api/pos/cloture/current
     * Get live summary of the current session before closing.
     */
    public function currentClotureSession(Request $request): JsonResponse
    {
        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $fondInitial = $request->has('fond_initial') ? (float)$request->get('fond_initial') : null;
        $summary = $this->clotureService->getCurrentSession($tenantId, auth()->user(), $fondInitial);
        return response()->json($summary);
    }

    /**
     * POST /api/pos/cloture
     * Perform cash closure (Rapport Z).
     */
    public function cloturerCaisse(Request $request): JsonResponse
    {
        $request->validate([
            'fond_initial'  => 'nullable|numeric|min:0',
            'total_declare' => 'required|numeric|min:0',
            'observations'  => 'nullable|string',
        ]);

        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $cloture = $this->clotureService->cloturer($tenantId, auth()->user(), $request->all());

        return response()->json([
            'success'       => true,
            'message'       => 'Caisse clôturée avec succès (Rapport Z généré)',
            'cloture'       => $cloture,
            'rapport_texte' => $cloture->rapport_texte,
        ], 201);
    }

    /**
     * GET /api/pos/cloture/history
     * Get past Z-reports.
     */
    public function clotureHistory(Request $request): JsonResponse
    {
        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $history = $this->clotureService->getHistoriqueClotures($tenantId, 50);
        return response()->json($history);
    }

    /**
     * GET /api/pos/cloture/{id}
     * Get details of a specific Z-report.
     */
    public function clotureDetail(Request $request, $id): JsonResponse
    {
        $tenantId = $request->get('current_tenant')->id ?? auth()->user()->tenant_id;
        $this->clotureService->ensureTableExists();

        $cloture = PosCloture::where('tenant_id', $tenantId)->find($id);
        if (!$cloture) {
            return response()->json(['message' => 'Rapport Z introuvable'], 404);
        }

        return response()->json($cloture);
    }
}
