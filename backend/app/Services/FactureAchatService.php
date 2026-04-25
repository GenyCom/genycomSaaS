<?php

namespace App\Services;

use App\Models\BR;
use App\Models\FactureAchat;
use App\Models\FactureAchatLigne;
use App\Models\Fournisseur;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FactureAchatService
{
    public function __construct(private NumerotationService $numerotation) {}

    /**
     * Génère une facture d'achat depuis un ou plusieurs BR.
     */
    public function genererFactureDepuisBR(array $idsBR, int $userId): FactureAchat
    {
        return DB::transaction(function () use ($idsBR, $userId) {
            $receptions = BR::whereIn('id', $idsBR)->with('lignes')->get();
            if ($receptions->isEmpty()) throw new \Exception("Aucun bon de réception trouvé.");

            $firstBR = $receptions->first();
            $tenantId = $firstBR->tenant_id;
            $fournisseurId = $firstBR->fournisseur_id;

            $numeroFA = $this->numerotation->generer($tenantId, 'FACTURE_ACHAT');

            // 1. Créer la Facture
            $facture = FactureAchat::create([
                'tenant_id'      => $tenantId,
                'numero'         => $numeroFA,
                'fournisseur_id' => $fournisseurId,
                'date_facture'   => now(),
                'date_echeance'  => now()->addDays(30), // Valeur temporaire, écrasée lors de la validation
                'devise_id'      => $firstBR->devise_id,
                'taux_change_document' => $firstBR->taux_change_document ?? 1.0,
                'statut'         => 'brouillon',
                'created_by'     => $userId
            ]);

            // 2. Lier les BR à la facture
            $facture->receptionNotes()->attach($idsBR);

            $totalHT = 0; $totalTVA = 0;

            // 3. Importer les lignes des BR vers la Facture
            foreach ($receptions as $br) {
                foreach ($br->lignes as $ligneBR) {
                    $tauxTva = 20.00;
                    // Tente de récupérer la TVA exacte si le produit existe
                    if ($ligneBR->produit_id && $ligneBR->produit) {
                        $tauxTva = (float) ($ligneBR->produit->taux_tva ?? 20.00);
                    }

                    $ht = $ligneBR->quantite_recue * $ligneBR->prix_unitaire;
                    $tva = $ht * ($tauxTva / 100);
                    $ttc = $ht + $tva;

                    FactureAchatLigne::create([
                        'facture_achat_id' => $facture->id,
                        'produit_id'       => $ligneBR->produit_id,
                        'designation'      => $ligneBR->designation,
                        'quantite'         => $ligneBR->quantite_recue,
                        'prix_unitaire'    => $ligneBR->prix_unitaire,
                        'taux_tva'         => $tauxTva,
                        'montant_ht'       => $ht,
                        'montant_tva'      => $tva,
                        'montant_ttc'      => $ttc
                    ]);

                    $totalHT += $ht;
                    $totalTVA += $tva;
                }
            }

            // 4. Mettre à jour les totaux globaux de la facture
            $facture->update([
                'montant_ht'    => $totalHT,
                'montant_tva'   => $totalTVA,
                'montant_ttc'   => $totalHT + $totalTVA,
                'reste_a_payer' => $totalHT + $totalTVA
            ]);

            // 5. OFFICIALISATION : On valide la facture pour générer la dette instantanément !
            $this->validerFacture($facture);

            return $facture;
        });
    }

    /**
     * Valide la facture, calcule les échéances et officialise la dette fournisseur.
     */
    public function validerFacture(FactureAchat $facture): void
    {
        DB::transaction(function () use ($facture) {
            // 1. Marquer la facture comme validée
            $facture->update(['statut' => 'valide']);

            // 2. Récupérer le délai de paiement accordé par le fournisseur
            $fournisseur = Fournisseur::find($facture->fournisseur_id);
            $joursDelai = $fournisseur->delai_paiement ?? 0;

            // 3. Calculer la vraie date d'échéance et l'enregistrer sur la facture
            $dateEcheance = Carbon::parse($facture->date_facture)->addDays($joursDelai);
            $facture->update(['date_echeance' => $dateEcheance]);

            // 4. Récupérer l'ID du premier BR lié pour assurer la traçabilité complète de l'origine
            $firstBrId = $facture->receptionNotes()->first()->id ?? null;

            // 5. GÉNÉRATION DE LA DETTE EN BASE DE DONNÉES
            DB::connection('tenant')->insert(
                "INSERT INTO dettes_fournisseur 
                (tenant_id, numero, fournisseur_id, facture_id, bon_reception_id, date_dette, date_echeance, montant_ht, montant_tva, montant_total, montant_restant, montant_regle, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())",
                [
                    $facture->tenant_id,
                    'DT-' . $facture->numero, // Ex: DT-FAC-2026-0001
                    $facture->fournisseur_id,
                    $facture->id,
                    $firstBrId,
                    $facture->date_facture,
                    $dateEcheance,
                    $facture->montant_ht,
                    $facture->montant_tva,
                    $facture->montant_ttc,
                    $facture->montant_ttc // Au début, le montant restant est 100% du total
                ]
            );
        });
    }
}