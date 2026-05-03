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
        // Debugging logs to identify who is calling this method
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = isset($trace[1]['class']) ? "{$trace[1]['class']}@{$trace[1]['function']}" : "unknown";
        \Log::info("STOCK_MVT: Calling enregistrerMouvement", [
            'caller'   => $caller,
            'produit'  => $produitId,
            'qty'      => $quantite,
            'entrepot' => $entrepotId,
            'doc'      => "$documentType #$documentId"
        ]);

        return DB::transaction(function () use ($produitId, $quantite, $typeMouvement, $documentType, $documentId, $userId, $tenantId, $entrepotId) {
            
            // Si aucun entrepôt n'est spécifié, on lève une exception pour forcer la sélection
            if (!$entrepotId) {
                throw new Exception("ERREUR CRITIQUE : entrepot_id manquant pour le mouvement de stock (Doc: $documentType #$documentId).");
            }

            // 1. Récupérer ou créer la ligne de stock pour cet entrepôt spécifique
            $stock = Stock::firstOrCreate(
                ['produit_id' => $produitId, 'entrepot_id' => $entrepotId, 'tenant_id' => $tenantId],
                ['quantite' => 0]
            );

            // 2. Gestion de la variation
            $isSortie = in_array($typeMouvement, ['sortie_vente', 'sortie_retour', 'ajustement_negatif', 'transfert_out']);
            $variation = $isSortie ? -$quantite : $quantite;

            // 3. Mise à jour physique
            $stock->quantite += $variation;
            $stock->save();

            // 4. Traçabilité (Mouvement)
            return MouvementStock::create([
                'tenant_id'          => $tenantId,
                'stock_id'           => $stock->id,
                'produit_id'         => $produitId,
                'type_mouvement'     => $typeMouvement,
                'quantite'           => $quantite,
                'document_type'      => $documentType,
                'document_id'        => $documentId,
                'created_by'         => $userId,
                'entrepot_source_id' => ($typeMouvement === 'transfert_out') ? $entrepotId : null,
                'entrepot_dest_id'   => ($typeMouvement === 'transfert_in') ? $entrepotId : null,
                'libelle'            => "Mouvement via $documentType #$documentId (" . ($isSortie ? 'Sortie' : 'Entrée') . ") - Caller: $caller"
            ]);
        });
    }

    /**
     * [OBSOLÈTE] Préférer appeler enregistrerMouvement directement depuis les contrôleurs pour passer l'entrepot_id.
     */
    public function validerBonReception($brId, $userId)
    {
        \Log::warning("DEPRECATED validerBonReception called for BR #$brId");
        $lignes = LigneBonReception::where('br_id', $brId)->get();
        foreach ($lignes as $ligne) {
            $this->enregistrerMouvement($ligne->produit_id, $ligne->quantite_recue, 'entree_achat', 'BR', $brId, $userId, $ligne->tenant_id);
        }
    }

    /**
     * [OBSOLÈTE] Préférer appeler enregistrerMouvement directement depuis les contrôleurs pour passer l'entrepot_id.
     */
    public function _CRASH_TEST_validerBonLivraison($blId, $userId)
    {
        \Log::warning("DEPRECATED validerBonLivraison called for BL #$blId");
        $lignes = LigneBonLivraison::where('bon_livraison_id', $blId)->get();
        foreach ($lignes as $ligne) {
            $this->enregistrerMouvement($ligne->produit_id, $ligne->quantite_livree, 'sortie_vente', 'BL', $blId, $userId, $ligne->tenant_id);
        }
    }

    public function validerAvoirClient($avoirId, $userId)
    {
        $avoir = \App\Models\AvoirClient::findOrFail($avoirId);
        $lignes = LigneAvoirClient::where('avoir_id', $avoirId)->get();
        foreach ($lignes as $ligne) {
            if (!$ligne->produit_id) continue;
            $this->enregistrerMouvement($ligne->produit_id, $ligne->quantite, 'entree_retour', 'AVOIR_CLIENT', $avoirId, $userId, $avoir->tenant_id);
        }
    }

    public function validerAvoirFournisseur($avoirId, $userId)
    {
        $avoir = \App\Models\AvoirFournisseur::findOrFail($avoirId);
        $lignes = LigneAvoirFournisseur::where('avoir_achat_id', $avoirId)->get();
        foreach ($lignes as $ligne) {
            if (!$ligne->produit_id) continue;
            $this->enregistrerMouvement($ligne->produit_id, $ligne->quantite, 'sortie_retour', 'AVOIR_FOURNISSEUR', $avoirId, $userId, $avoir->tenant_id);
        }
    }

    public function ajusterManual($produitId, $entrepotId, $quantite, $type, $userId, $tenantId = 1)
    {
        return $this->enregistrerMouvement($produitId, $quantite, $type, 'MANUAL', null, $userId, $tenantId, $entrepotId);
    }
}
