<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Service métier du cycle d'achat — 100% requêtes SQL natives.
 * BCF (Bon de Commande Fournisseur) → BR (Bon de Réception) → Dette Fournisseur
 *
 * NOTE: Dans l'architecture multi-base, les tables de réception et dettes
 * n'ont pas de colonne tenant_id, contrairement aux produits et stocks. v2
 */
class CycleAchatService
{
    public function __construct(
        private NumerotationService $numerotation,
        private StockService $stockService
    ) {}

    private function db()
    {
        return DB::connection('tenant');
    }

    /**
     * Transforme un BCF en Bon de Réception, incrémente le stock.
     */
    public function transformerBcfEnBr(int $tenantId, int $idBcf, int $userId): array
    {
        \Log::info("Transformation BCF -> BR lancée", ['tenant' => $tenantId, 'bcf' => $idBcf]);
        $this->db()->beginTransaction();
        try {
            // 1. Lire le BCF
            $bcf = $this->db()->select(
                "SELECT * FROM bcf WHERE id = ? LIMIT 1",
                [$idBcf]
            );
            if (empty($bcf)) {
                throw new \Exception("Commande fournisseur #{$idBcf} introuvable.");
            }
            $bcf = $bcf[0];

            // 2. Générer le numéro du BR
            $numeroBr = $this->numerotation->generer($tenantId, 'BR');

            // 3. Insérer le Bon de Réception
            $this->db()->insert(
                "INSERT INTO br (numero, date_reception, bcf_id, fournisseur_id, observations, statut, devise_id, taux_change_document, created_by, created_at)
                 VALUES (?, NOW(), ?, ?, ?, 'valide', ?, ?, ?, NOW())",
                [$numeroBr, $bcf->id, $bcf->fournisseur_id, $bcf->observations, $bcf->devise_id, $bcf->taux_change_document ?? 1.0, $userId]
            );
            $brId = $this->db()->getPdo()->lastInsertId();

            // 4. Copier les lignes
            $lignes = $this->db()->select(
                "SELECT * FROM bcf_lignes WHERE bcf_id = ? ORDER BY ordre",
                [$idBcf]
            );

            foreach ($lignes as $ligne) {
                $this->db()->insert(
                    "INSERT INTO br_lignes (br_id, produit_id, designation, quantite_commandee, quantite_recue, prix_unitaire, ordre, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $brId, $ligne->produit_id, $ligne->designation,
                        $ligne->quantite, $ligne->quantite,
                        $ligne->prix_unitaire, $ligne->ordre
                    ]
                );

                // 5. Stock (Gestion centralisée AVEC tenant_id)
                if ($ligne->produit_id) {
                    $this->incrementerStock($tenantId, (int) $ligne->produit_id, (float) $ligne->quantite, [
                        'reference_document' => $numeroBr,
                        'document_type'      => 'BonReception',
                        'document_id'        => $brId,
                        'prix_unitaire'      => $ligne->prix_unitaire,
                        'taux_change'        => $bcf->taux_change_document ?? 1.0,
                        'user_id'            => $userId,
                    ]);
                }
            }

            // 6. Marquer le BCF comme livré via etat_id
            $etatRecu = $this->db()->table('etat_document')
                ->where('type_document', 'bcf')
                ->where('code', 'RECU')
                ->first();
            
            if ($etatRecu) {
                $this->db()->update(
                    "UPDATE bcf SET etat_id = ?, updated_at = NOW() WHERE id = ?",
                    [$etatRecu->id, $idBcf]
                );
            }

            $this->db()->commit();
            return ['id' => (int) $brId, 'numero' => $numeroBr];

        } catch (\Throwable $e) {
            $this->db()->rollBack();
            throw $e;
        }
    }

    /**
     * Valide un BR et crée une dette fournisseur.
     */
    public function validerBrEtCreerDette(int $tenantId, int $idBr, int $userId): array
    {
        $this->db()->beginTransaction();
        try {
            $br = $this->db()->select("SELECT * FROM br WHERE id = ? LIMIT 1", [$idBr]);
            if (empty($br)) throw new \Exception("BR #{$idBr} introuvable.");
            $br = $br[0];

            // Calcul totaux
            $totaux = $this->db()->select(
                "SELECT COALESCE(SUM(quantite_recue * prix_unitaire), 0) AS total_ht FROM br_lignes WHERE br_id = ?",
                [$idBr]
            );
            $totalHt  = (float) ($totaux[0]->total_ht ?? 0);
            $totalTtc = $totalHt * 1.20; // Défaut 20% si pas d'infos commande (TVA à affiner)

            $numeroDette = $this->numerotation->generer($tenantId, 'DETTE');

            // 4. Insérer Dette (incluant devise et taux hérité du BR)
            $this->db()->insert(
                "INSERT INTO factures_achats
                    (tenant_id, numero, fournisseur_id, bcf_id, br_id,
                     date_facture, date_echeance, montant_ht, montant_tva, montant_ttc,
                     montant_paye, reste_a_payer, devise_id, taux_change_document, created_by, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), ?, ?, ?, 0, ?, ?, ?, ?, NOW())",
                [
                    $tenantId, $numeroDette, $br->fournisseur_id, $br->bcf_id ?? null, $idBr,
                    $totalHt, ($totalTtc - $totalHt), $totalTtc, $totalTtc, 
                    $br->devise_id, $br->taux_change_document ?? 1.0, $userId
                ]
            );
            $detteId = $this->db()->getPdo()->lastInsertId();

            $this->db()->commit();
            return ['id' => (int) $detteId, 'numero' => $numeroDette];

        } catch (\Throwable $e) {
            $this->db()->rollBack();
            throw $e;
        }
    }

    /**
     * Supprime un BR et annule les mouvements de stock.
     */
    public function supprimerBr(int $tenantId, int $idBr, int $userId): void
    {
        $this->db()->beginTransaction();
        try {
            $br = $this->db()->select("SELECT * FROM br WHERE id = ? LIMIT 1", [$idBr]);
            if (empty($br)) throw new \Exception("Bon de réception introuvable.");
            $br = $br[0];

            // Vérifier si une facture est liée
            $facture = $this->db()->select("SELECT id FROM factures_achats WHERE br_id = ? LIMIT 1", [$idBr]);
            if (!empty($facture)) {
                throw new \Exception("Impossible de supprimer ce bon car il a déjà été facturé.");
            }

            // Récupérer les lignes pour annuler le stock
            $lignes = $this->db()->select("SELECT * FROM br_lignes WHERE br_id = ?", [$idBr]);

            foreach ($lignes as $ligne) {
                if ($ligne->produit_id) {
                    // On fait un incrément négatif pour déduire du stock
                    $this->incrementerStock($tenantId, (int) $ligne->produit_id, -((float) $ligne->quantite_recue), [
                        'reference_document' => 'ANNULATION ' . $br->numero,
                        'document_type'      => 'BonReception',
                        'document_id'        => $idBr,
                        'user_id'            => $userId,
                        'type_mouvement'     => 'annulation_achat'
                    ]);
                }
            }

            // Supprimer les lignes et le BR
            $this->db()->delete("DELETE FROM br_lignes WHERE br_id = ?", [$idBr]);
            $this->db()->delete("DELETE FROM br WHERE id = ?", [$idBr]);

            // Si lié à un BCF, remettre le BCF en état non reçu (optionnel)
            if ($br->bcf_id) {
                $etatBrouillon = $this->db()->table('etat_document')
                    ->where('type_document', 'bcf')
                    ->where('code', 'BROUILLON')
                    ->first();
                if ($etatBrouillon) {
                    $this->db()->update("UPDATE bcf SET etat_id = ? WHERE id = ?", [$etatBrouillon->id, $br->bcf_id]);
                }
            }

            $this->db()->commit();
        } catch (\Throwable $e) {
            $this->db()->rollBack();
            throw $e;
        }
    }

    public function incrementerStock(int $tenantId, int $produitId, float $quantite, array $options = []): void
    {
        $entrepotId = $this->stockService->getDefaultEntrepotId($tenantId);

        // On garde tenant_id pour stocks/mouvements car ils l'ont
        $stock = $this->db()->select(
            "SELECT id FROM stocks WHERE tenant_id = ? AND produit_id = ? AND entrepot_id = ? LIMIT 1",
            [$tenantId, $produitId, $entrepotId]
        );

        $typeMvmt = $options['type_mouvement'] ?? 'entree_achat';

        if (empty($stock)) {
            $this->db()->insert(
                "INSERT INTO stocks (tenant_id, produit_id, entrepot_id, quantite, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$tenantId, $produitId, $entrepotId, $quantite]
            );
            $stockId = $this->db()->getPdo()->lastInsertId();
        } else {
            $stockId = $stock[0]->id;
            $this->db()->update(
                "UPDATE stocks SET quantite = quantite + ?, updated_at = NOW() WHERE id = ?",
                [$quantite, $stockId]
            );
        }

        $this->db()->insert(
            "INSERT INTO mouvements_stock
                (tenant_id, stock_id, produit_id, type_mouvement, quantite, libelle,
                 reference_document, document_type, document_id, prix_unitaire, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $tenantId, $stockId, $produitId, $typeMvmt, $quantite,
                $options['libelle'] ?? "Réception fournisseur: " . ($options['reference_document'] ?? ''),
                $options['reference_document'] ?? null,
                $options['document_type'] ?? null,
                $options['document_id'] ?? null,
                $options['prix_unitaire'] ?? null,
                $options['user_id'] ?? null,
            ]
        );

        // Calcul du PUMP (Prix Unitaire Moyen Pondéré) - Uniquement si entrée positive
        if ($quantite > 0) {
            $produit = $this->db()->select(
                "SELECT stock_actuel, prix_revient FROM produits WHERE id = ? AND tenant_id = ? LIMIT 1",
                [$produitId, $tenantId]
            );

            if (!empty($produit)) {
                $p = $produit[0];
                $oldStock = (float) $p->stock_actuel;
                $oldPump  = (float) $p->prix_revient;
                $newQty   = (float) $quantite;
                $newPrice = (float) ($options['prix_unitaire'] ?? 0) * (float) ($options['taux_change'] ?? 1.0);

                if ($oldStock <= 0) {
                    $newPump = $newPrice;
                } else {
                    $newPump = (($oldStock * $oldPump) + ($newQty * $newPrice)) / ($oldStock + $newQty);
                }

                $this->db()->update(
                    "UPDATE produits SET prix_revient = ? WHERE id = ? AND tenant_id = ?",
                    [$newPump, $produitId, $tenantId]
                );
            }
        }

        // Mettre à jour stock_actuel dans Produits
        $totalStock = $this->db()->select(
            "SELECT COALESCE(SUM(quantite), 0) AS total FROM stocks WHERE tenant_id = ? AND produit_id = ?",
            [$tenantId, $produitId]
        );

        $this->db()->update(
            "UPDATE produits SET stock_actuel = ? WHERE id = ? AND tenant_id = ?",
            [(float) $totalStock[0]->total, $produitId, $tenantId]
        );
    }
}
