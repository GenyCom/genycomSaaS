<?php
namespace App\Traits;

/**
 * Trait pour les calculs de lignes de document (facture, devis, commande, avoir).
 */
trait HasLigneCalculs
{
    /**
     * Recalcule les montants de la ligne à partir de quantité, prix unitaire, TVA et remise.
     */
    public function recalculer(): self
    {
        $sousTotal = $this->quantite * $this->prix_unitaire;
        
        // Appliquer la remise
        if ($this->remise_pourcent > 0) {
            $this->remise_montant = round($sousTotal * ($this->remise_pourcent / 100), 2);
        }
        
        $this->montant_ht = round($sousTotal - ($this->remise_montant ?? 0), 2);
        $this->montant_tva = round($this->montant_ht * ($this->taux_tva / 100), 2);
        $this->montant_ttc = round($this->montant_ht + $this->montant_tva, 2);
        
        return $this;
    }

    /**
     * Boot : auto-recalcul avant sauvegarde.
     */
    protected static function bootHasLigneCalculs(): void
    {
        static::saving(function ($model) {
            $model->recalculer();
        });
    }
}
