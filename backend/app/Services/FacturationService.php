<?php
namespace App\Services;

use App\Models\{Facture, LigneFacture, Devis, EtatDocument, Contrat};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Service de facturation — gestion du cycle de vie des factures.
 */
class FacturationService
{
    public function __construct(
        private NumerotationService $numerotation,
        private StockService $stockService,
    ) {}

    /**
     * Créer une facture depuis un devis existant.
     */
    public function creerDepuisDevis(Devis $devis): Facture
    {
        return DB::transaction(function () use ($devis) {
            $etatBrouillon = EtatDocument::where('tenant_id', $devis->tenant_id)
                ->ofType('facture')->byCode('BRL')->first();

            $facture = Facture::create([
                'tenant_id'         => $devis->tenant_id,
                'numero'            => $this->numerotation->generer($devis->tenant_id, 'FACTURE'),
                'date_facture'      => now()->toDateString(),
                'client_id'         => $devis->client_id,
                'projet_id'         => $devis->projet_id,
                'devis_id'          => $devis->id,
                'total_ht'          => $devis->total_ht,
                'total_tva'         => $devis->total_tva,
                'total_ttc'         => $devis->total_ttc,
                'total_remise'      => $devis->total_remise,
                'montant_restant'   => $devis->total_ttc,
                'etat_id'           => $etatBrouillon?->id,
                'devise_id'         => $devis->devise_id,
            ]);

            // Dupliquer les lignes du devis
            foreach ($devis->lignes as $index => $ligne) {
                LigneFacture::create([
                    'facture_id'        => $facture->id,
                    'produit_id'        => $ligne->produit_id,
                    'designation'       => $ligne->designation,
                    'description'       => $ligne->description,
                    'quantite'          => $ligne->quantite,
                    'unite'             => $ligne->unite,
                    'prix_unitaire'     => $ligne->prix_unitaire,
                    'taux_tva'          => $ligne->taux_tva,
                    'remise_pourcent'   => $ligne->remise_pourcent,
                    'remise_montant'    => $ligne->remise_montant,
                    'montant_ht'        => $ligne->montant_ht,
                    'montant_tva'       => $ligne->montant_tva,
                    'montant_ttc'       => $ligne->montant_ttc,
                    'ordre'             => $ligne->ordre,
                    'source_type'       => 'depuis_devis',
                    'is_produit_fini'   => $ligne->is_produit_fini,
                ]);
            }

            // Marquer le devis comme facturé
            $etatFacture = EtatDocument::where('tenant_id', $devis->tenant_id)
                ->ofType('devis')->byCode('FAC')->first();
            $devis->update([
                'est_facture' => true,
                'etat_id' => $etatFacture?->id,
            ]);

            return $facture->fresh('lignes', 'client', 'etat');
        });
    }

    /**
     * Générer une facture à partir d'un contrat récurrent.
     */
    public function genererDepuisContrat(Contrat $contrat, ?string $dateFacture = null): Facture
    {
        return DB::transaction(function () use ($contrat, $dateFacture) {
            $date = $dateFacture ?? $contrat->prochaine_echeance->toDateString();
            
            $etatBrouillon = EtatDocument::where('tenant_id', $contrat->tenant_id)
                ->ofType('facture')->byCode('BRL')->first();

            $facture = Facture::create([
                'tenant_id'       => $contrat->tenant_id,
                'numero'          => $this->numerotation->generer($contrat->tenant_id, 'FACTURE'),
                'date_facture'    => $date,
                'client_id'       => $contrat->client_id,
                'total_ht'        => $contrat->total_ht,
                'total_tva'       => $contrat->total_tva,
                'total_ttc'       => $contrat->total_ttc,
                'montant_restant' => $contrat->total_ttc,
                'etat_id'         => $etatBrouillon?->id,
                'observations'    => "Facturation récurrente pour : " . $contrat->titre,
            ]);

            foreach ($contrat->lignes as $ligne) {
                LigneFacture::create([
                    'facture_id'    => $facture->id,
                    'produit_id'    => $ligne->produit_id,
                    'designation'   => $ligne->designation,
                    'quantite'      => $ligne->quantite,
                    'prix_unitaire' => $ligne->prix_unitaire,
                    'taux_tva'      => $ligne->taux_tva,
                    'montant_ht'    => $ligne->montant_ht,
                    'montant_tva'   => $ligne->montant_tva,
                    'montant_ttc'   => $ligne->montant_ttc,
                    'ordre'         => $ligne->ordre,
                    'source_type'   => 'abonnement',
                ]);
            }

            // Mise à jour de la prochaine échéance
            $next = Carbon::parse($contrat->prochaine_echeance);
            switch ($contrat->frequence) {
                case 'MENSUEL':     $next->addMonth(); break;
                case 'TRIMESTRIEL': $next->addMonths(3); break;
                case 'SEMESTRIEL':  $next->addMonths(6); break;
                case 'ANNUEL':      $next->addYear(); break;
            }
            
            $contrat->update(['prochaine_echeance' => $next]);

            return $facture;
        });
    }

    /**
     * Ajouter une ligne manuellement à une facture.
     */
    public function ajouterLigne(Facture $facture, array $data): LigneFacture
    {
        return DB::transaction(function () use ($facture, $data) {
            $data['facture_id'] = $facture->id;
            $data['source_type'] = $data['source_type'] ?? 'saisie_manuelle';
            $data['ordre'] = $data['ordre'] ?? ($facture->lignes()->max('ordre') + 1);
            
            $ligne = LigneFacture::create($data);
            $facture->recalculerTotaux();
            
            return $ligne;
        });
    }

    /**
     * Simuler les échéances de contrats pour une date donnée.
     */
    public function simulerEcheances(string $date): \Illuminate\Support\Collection
    {
        return Contrat::with(['client', 'lignes'])
            ->where('statut', 'ACTIF')
            ->whereDate('prochaine_echeance', '<=', $date)
            ->get();
    }

    /**
     * Valider et ouvrir une facture (brouillon → ouverte).
     */
    public function valider(Facture $facture): Facture
    {
        return DB::transaction(function () use ($facture) {
            $etatOuverte = EtatDocument::where('tenant_id', $facture->tenant_id)
                ->ofType('facture')->byCode('OVR')->first();
            
            $facture->update(['etat_id' => $etatOuverte?->id]);
            
            // Calculer la date d'échéance si condition de règlement définie
            if ($facture->condition_reglement_id && !$facture->date_echeance) {
                $jours = $facture->conditionReglement->nombre_jours ?? 0;
                $facture->update([
                    'date_echeance' => $facture->date_facture->addDays($jours),
                ]);
            }

            return $facture->fresh();
        });
    }

    /**
     * Recalculer tous les totaux d'une facture.
     */
    public function recalculerTotaux(Facture $facture): Facture
    {
        return $facture->recalculerTotaux();
    }

    /**
     * Enregistrer un règlement sur une facture.
     */
    public function enregistrerReglement(Facture $facture, array $data): void
    {
        DB::transaction(function () use ($facture, $data) {
            // Créer l'entrée de règlement
            $facture->reglements()->create([
                'tenant_id'         => $facture->tenant_id,
                'date_reglement'    => $data['date_reglement'],
                'montant'           => $data['montant'],
                'mode_reglement_id' => $data['mode_reglement_id'] ?? null,
                'numero_cheque'     => $data['numero_cheque'] ?? null,
                'banque'            => $data['banque'] ?? null,
                'reference_virement'=> $data['reference_virement'] ?? null,
                'observations'      => $data['observations'] ?? null,
            ]);

            // Mettre à jour la facture
            $facture->enregistrerReglement($data['montant']);
            
            // Mettre à jour l'encours du client
            $client = $facture->client;
            $client->montant_rest_du = Facture::where('client_id', $client->id)
                ->where('est_reglee', false)
                ->sum(DB::raw('total_ttc - montant_regle'));
            $client->save();
        });
    }
}
