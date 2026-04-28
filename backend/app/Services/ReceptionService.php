<?php

namespace App\Services;

use App\Models\BonCommandeFournisseur;
use App\Models\LigneBonCommandeFournisseur;
use App\Models\BR;
use App\Models\LigneBonReception;
use App\Models\Stock;
use App\Models\MouvementStock;
use App\Models\Produit;
use Illuminate\Support\Facades\DB;

class ReceptionService
{
    public function __construct(
        private NumerotationService $numerotation,
        private StockService $stockService
    ) {}

    /**
     * Traite la réception d'une commande (BCF). v5
     * @param BCF $commande L'objet BCF
     * @param array $quantitesRecues Mapping [bcf_ligne_id => quantite_recue]
     */
    public function receptionnerCommande(BonCommandeFournisseur $commande, array $quantitesRecues, int $userId): BR
    {
        return DB::transaction(function () use ($commande, $quantitesRecues, $userId) {
            $tenantId = $commande->tenant_id;
            $numeroBr = $this->numerotation->generer($tenantId, 'BR');

            // 1. Créer le BR
            $br = BR::create([
                'tenant_id'      => $tenantId,
                'bcf_id'    	 => $commande->id,
                'numero'         => $numeroBr,
                'date_reception' => now(),
                'fournisseur_id' => $commande->fournisseur_id,
                'devise_id'      => $commande->devise_id,
                'taux_change_document' => $commande->taux_change_document ?? 1.0,
                'statut'         => 'valide',
                'created_by'     => $userId
            ]);

            $totalReceptionne = true;

            // 2. Créer les lignes de BR et mettre à jour les stocks
            foreach ($commande->lignes as $ligneBCF) {
                $qRecue = (float) ($quantitesRecues[$ligneBCF->id] ?? 0);
                
                if ($qRecue > 0) {
                    LigneBonReception::create([
                        'br_id'              => $br->id,
                        'produit_id'         => $ligneBCF->produit_id,
                        'designation'        => $ligneBCF->designation,
                        'quantite_commandee' => $ligneBCF->quantite,
                        'quantite_recue'     => $qRecue,
                        'prix_unitaire'      => $ligneBCF->prix_unitaire,
                        'ordre'              => $ligneBCF->ordre
                    ]);

                    // Mise à jour Stock
                    if ($ligneBCF->produit_id) {
                        $this->incrementerStock($tenantId, $ligneBCF->produit_id, $qRecue, [
                            'ref' => $numeroBr,
                            'br_id' => $br->id,
                            'user_id' => $userId,
                            'prix' => $ligneBCF->prix_unitaire,
                            'taux_change' => $br->taux_change_document ?? 1.0,
                        ]);
                    }
                }

                // Vérification reliquats
                if ($qRecue < $ligneBCF->quantite) {
                    $totalReceptionne = false;
                }
            }

            // 3. Mettre à jour le statut du BCF
            $commande->statut = $totalReceptionne ? 'clos' : 'reception_partielle';
            $commande->save();

            return $br;
        });
    }

    private function incrementerStock(int $tenantId, int $produitId, float $quantite, array $meta): void
    {
        $entrepotId = $this->stockService->getDefaultEntrepotId($tenantId);

        $stock = Stock::firstOrCreate(
            ['tenant_id' => $tenantId, 'produit_id' => $produitId, 'entrepot_id' => $entrepotId],
            ['quantite' => 0]
        );

        $stock->increment('quantite', $quantite);

        MouvementStock::create([
            'tenant_id'     => $tenantId,
            'stock_id'      => $stock->id,
            'produit_id'    => $produitId,
            'type_mouvement'=> 'entree_achat',
            'quantite'      => $quantite,
            'libelle'       => "Réception fournisseur {$meta['ref']}",
            'reference_document' => $meta['ref'],
            'document_type' => 'BR',
            'document_id'   => $meta['br_id'],
            'prix_unitaire' => $meta['prix'],
            'created_by'    => $meta['user_id']
        ]);

        // Mettre à jour PUMP (Prix Unitaire Moyen Pondéré) et stock_actuel dans Produits
        $produit = Produit::where('id', $produitId)->where('tenant_id', $tenantId)->first();
        if ($produit) {
            $oldStock = (float) $produit->stock_actuel;
            $oldPump  = (float) $produit->prix_revient;
            $newQty   = (float) $quantite;
            $newPriceLocal = (float) ($meta['prix'] ?? 0) * (float) ($meta['taux_change'] ?? 1.0);

            if ($oldStock <= 0) {
                $newPump = $newPriceLocal;
            } else {
                $newPump = (($oldStock * $oldPump) + ($newQty * $newPriceLocal)) / ($oldStock + $newQty);
            }

            $produit->stock_actuel = Stock::where('tenant_id', $tenantId)->where('produit_id', $produitId)->sum('quantite');
            $produit->prix_revient = $newPump;
            $produit->save();
        }
    }
}
