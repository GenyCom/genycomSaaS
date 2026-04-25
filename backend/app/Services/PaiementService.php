<?php

namespace App\Services;

use App\Models\FactureAchat;
use App\Models\PaiementFournisseur;
use Illuminate\Support\Facades\DB;

class PaiementService
{
    /**
     * Enregistre un paiement pour une facture d'achat.
     */
    public function enregistrerPaiement(FactureAchat $facture, float $montant, string $moyenPaiement, int $userId, array $options = []): PaiementFournisseur
    {
        return DB::transaction(function () use ($facture, $montant, $moyenPaiement, $userId, $options) {
            
            // 1. Créer l'entrée de paiement
            $paiement = PaiementFournisseur::create([
                'tenant_id'        => $facture->tenant_id,
                'facture_achat_id' => $facture->id,
                'date_paiement'    => now(),
                'montant'          => $montant,
                'mode_paiement'    => $moyenPaiement,
                'reference'        => $options['reference'] ?? null,
                'observations'     => $options['observations'] ?? null,
                'created_by'       => $userId
            ]);

            // 2. Mettre à jour la facture (Finance)
            $newMontantPaye = $facture->montant_paye + $montant;
            $newResteAPayer = $facture->montant_ttc - $newMontantPaye;

            // Protection contre les arrondis
            if (abs($newResteAPayer) < 0.01) {
                $newResteAPayer = 0;
            }

            $statut = ($newResteAPayer <= 0) ? 'paye' : 'partiellement_paye';

            $facture->update([
                'montant_paye'  => $newMontantPaye,
                'reste_a_payer' => $newResteAPayer,
                'statut'        => $statut
            ]);

            return $paiement;
        });
    }
}
