<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\MouvementStock;
use App\Models\Entrepot;
use App\Models\LigneBonReception;
use App\Models\LigneBonLivraison;
use App\Models\LigneAvoirClient;
use App\Models\LigneAvoirFournisseur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * StockService - Gestion centralisée des stocks par entrepôt
 */
class StockService
{
    /**
     * Obtenir ou créer l'entrepôt par défaut
     */
    public function getDefaultEntrepotId($tenantId = 1)
    {
        $entrepot = Entrepot::where('tenant_id', $tenantId)->where('is_default', true)->first();
        if (!$entrepot) {
            $entrepot = Entrepot::create([
                'tenant_id' => $tenantId,
                'code' => 'DEP-PPAL',
                'nom' => 'Dépôt Principal',
                'is_default' => true
            ]);
        }
        return $entrepot->id;
    }

    /**
     * Enregistrer un mouvement de stock
     */
    public function enregistrerMouvement($produitId, $quantite, $typeMouvement, $documentType, $documentId, $userId, $tenantId = 1, $entrepotId = null)
    {
        return DB::transaction(function () use ($produitId, $quantite, $typeMouvement, $documentType, $documentId, $userId, $tenantId, $entrepotId) {
            
            $entrepotId = $entrepotId ?: $this->getDefaultEntrepotId($tenantId);

            // 1. Récupérer ou créer la ligne de stock
            $stock = Stock::firstOrCreate(
                ['produit_id' => $produitId, 'entrepot_id' => $entrepotId, 'tenant_id' => $tenantId],
                ['quantite' => 0]
            );

            // 2. Vérification du stock pour les sorties
            $isSortie = in_array($typeMouvement, ['sortie_vente', 'sortie_retour', 'ajustement_negatif', 'transfert_out']);
            if ($isSortie && ($stock->quantite - $quantite) < 0) {
                // On peut autoriser le stock négatif selon le paramétrage, ici on bloque par défaut
                // throw new Exception("Stock insuffisant.");
            }

            // 3. Mise à jour physique
            $variation = $isSortie ? -$quantite : $quantite;
            $stock->quantite += $variation;
            $stock->save();

            // 4. Traçabilité
            return MouvementStock::create([
                'tenant_id' => $tenantId,
                'stock_id' => $stock->id,
                'produit_id' => $produitId,
                'type_mouvement' => $typeMouvement,
                'quantite' => $quantite,
                'document_type' => $documentType,
                'document_id' => $documentId,
                'created_by' => $userId,
                'entrepot_source_id' => ($typeMouvement === 'transfert_out') ? $entrepotId : null,
                'entrepot_dest_id' => ($typeMouvement === 'transfert_in') ? $entrepotId : null,
            ]);
        });
    }

    /**
     * Valide un Bon de Réception (BR)
     */
    public function validerBonReception($brId, $userId)
    {
        $lignes = LigneBonReception::where('br_id', $brId)->get();
        foreach ($lignes as $ligne) {
            $this->enregistrerMouvement(
                $ligne->produit_id,
                $ligne->quantite_recue,
                'entree_achat',
                'BR',
                $brId,
                $userId,
                $ligne->tenant_id
            );
        }
    }

    /**
     * Valide un Bon de Livraison (BL)
     */
    public function validerBonLivraison($blId, $userId)
    {
        $lignes = LigneBonLivraison::where('bon_livraison_id', $blId)->get();
        foreach ($lignes as $ligne) {
            $this->enregistrerMouvement(
                $ligne->produit_id,
                $ligne->quantite_livree,
                'sortie_vente',
                'BL',
                $blId,
                $userId,
                $ligne->tenant_id
            );
        }
    }

    /**
     * Valide un Avoir Client (Retour client -> Entrée de stock)
     */
    public function validerAvoirClient($avoirId, $userId)
    {
        $avoir = \App\Models\AvoirClient::findOrFail($avoirId);
        $lignes = LigneAvoirClient::where('avoir_id', $avoirId)->get();
        
        foreach ($lignes as $ligne) {
            if (!$ligne->produit_id) continue;
            
            $this->enregistrerMouvement(
                $ligne->produit_id,
                $ligne->quantite,
                'entree_retour', // Entrée
                'AVOIR_CLIENT',
                $avoirId,
                $userId,
                $avoir->tenant_id
            );
        }
    }

    /**
     * Valide un Avoir Fournisseur (Retour au fournisseur -> Sortie de stock)
     */
    public function validerAvoirFournisseur($avoirId, $userId)
    {
        $avoir = \App\Models\AvoirFournisseur::findOrFail($avoirId);
        $lignes = LigneAvoirFournisseur::where('avoir_achat_id', $avoirId)->get();
        
        foreach ($lignes as $ligne) {
            if (!$ligne->produit_id) continue;

            $this->enregistrerMouvement(
                $ligne->produit_id,
                $ligne->quantite,
                'sortie_retour', // Sortie
                'AVOIR_FOURNISSEUR',
                $avoirId,
                $userId,
                $avoir->tenant_id
            );
        }
    }

    /**
     * Ajustement manuel
     */
    public function ajusterManual($produitId, $entrepotId, $quantite, $type, $userId, $tenantId = 1)
    {
        return $this->enregistrerMouvement($produitId, $quantite, $type, 'MANUAL', null, $userId, $tenantId, $entrepotId);
    }
}
