<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Models\BonCommandeClient;
use App\Models\LigneBonCommandeClient;
use App\Models\BonLivraison;
use App\Models\LigneBonLivraison;
use App\Models\Facture;
use App\Models\LigneFacture;
use App\Models\Devise;
use App\Models\EtatDocument;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class WorkflowVenteController extends Controller
{
    public function __construct(
        private NumerotationService $numerotation,
        private \App\Services\StockService $stockService
    ) {}

    /**
     * Transforme un Devis en Bon de Commande Client.
     */
    public function devisToBC(Request $request, Devis $devis): JsonResponse
    {
        return DB::transaction(function () use ($request, $devis) {
            $tenantId = $request->get('current_tenant')->id;
            
            $etatBrouillon = EtatDocument::where('type_document', 'bcc')->where('code', 'BRL')->first();

            $bcc = BonCommandeClient::create([
                'tenant_id'     => $tenantId,
                'numero'        => $this->numerotation->generer($tenantId, 'BCC'),
                'date_commande' => now(),
                'client_id'     => $devis->client_id,
                'devis_id'      => $devis->id,
                'devise_id'     => $devis->devise_id,
                'taux_change_document' => $devis->taux_change_document ?? 1.0,
                'observations'  => $devis->observations,
                'etat_id'       => $etatBrouillon?->id,
                'created_by'    => auth()->id() ?? $devis->created_by,
            ]);

            // 2. Copier les lignes
            foreach ($devis->lignes as $ligne) {
                LigneBonCommandeClient::create([
                    'tenant_id'              => $tenantId,
                    'bon_commande_client_id' => $bcc->id,
                    'produit_id'            => $ligne->produit_id,
                    'designation'           => $ligne->designation,
                    'description'           => $ligne->description,
                    'quantite'              => $ligne->quantite,
                    'unite'                 => $ligne->unite,
                    'prix_unitaire'         => $ligne->prix_unitaire,
                    'taux_tva'              => $ligne->taux_tva,
                    'remise_pourcent'       => $ligne->remise_pourcent,
                    'remise_montant'        => $ligne->remise_montant,
                    'montant_ht'            => $ligne->montant_ht,
                    'montant_tva'           => $ligne->montant_tva,
                    'montant_ttc'           => $ligne->montant_ttc,
                    'ordre'                 => $ligne->ordre,
                ]);
            }
            
            $bcc->recalculerTotaux();

            // 3. Mettre à jour le Devis
            $etatAccepte = EtatDocument::where('type_document', 'devis')->where('code', 'ACC')->first();
            if ($etatAccepte) {
                $devis->update(['etat_id' => $etatAccepte->id]);
            }

            return response()->json([
                'message' => 'Devis transformé en Bon de Commande avec succès',
                'id'      => $bcc->id,
                'numero'  => $bcc->numero
            ]);
        });
    }

    /**
     * Transforme un BC en Bon de Livraison.
     */
    public function bcToBL(Request $request, BonCommandeClient $bcc): JsonResponse
    {
        return DB::transaction(function () use ($request, $bcc) {
            $tenantId = $request->get('current_tenant')->id;

            $bl = BonLivraison::create([
                'tenant_id'              => $tenantId,
                'numero'                 => $this->numerotation->generer($tenantId, 'BL'),
                'date_livraison'         => now(),
                'client_id'              => $bcc->client_id,
                'devis_id'               => $bcc->devis_id,
                'projet_id'              => $bcc->projet_id,
                'bon_commande_client_id' => $bcc->id,
                'entrepot_id'            => $request->input('entrepot_id'),
                'statut'                 => 'valide',
                'total_ht'               => $bcc->total_ht,
                'total_tva'              => $bcc->total_tva,
                'total_ttc'              => $bcc->total_ttc,
                'total_remise'           => $bcc->total_remise,
                'devise_id'              => $bcc->devise_id,
                'taux_change_document'   => $bcc->taux_change_document ?? 1.0,
                'observations'           => $bcc->observations,
                'created_by'             => auth()->id() ?? $bcc->created_by,
            ]);

            foreach ($bcc->lignes as $ligne) {
                LigneBonLivraison::create([
                    'tenant_id'        => $tenantId,
                    'bon_livraison_id' => $bl->id,
                    'produit_id'       => $ligne->produit_id,
                    'designation'      => $ligne->designation,
                    'quantite_prevue'  => $ligne->quantite,
                    'quantite_livree'  => $ligne->quantite,
                    'prix_unitaire'    => $ligne->prix_unitaire,
                    'taux_tva'         => $ligne->taux_tva,
                    'montant_ht'       => $ligne->montant_ht,
                    'montant_tva'      => $ligne->montant_tva,
                    'montant_ttc'      => $ligne->montant_ttc,
                    'ordre'            => $ligne->ordre,
                ]);

                // Sortie de stock si c'est un produit (pas un service)
                if ($ligne->produit_id) {
                    \Log::info("WFV: bcToBL movement", ['entrepot' => $request->input('entrepot_id'), 'produit' => $ligne->produit_id]);
                    $this->stockService->enregistrerMouvement(
                        $ligne->produit_id,
                        $ligne->quantite,
                        'sortie_vente',
                        'BL',
                        $bl->id,
                        auth()->id(),
                        $tenantId,
                        $request->input('entrepot_id')
                    );
                }
            }

            // Désactivé car le BL n'a plus de montants financiers sur ses lignes
            // $bl->recalculerTotaux();

            $etatLivre = EtatDocument::where('type_document', 'bcc')->where('code', 'LIV')->first();
            $bcc->update(['est_livre' => true, 'etat_id' => $etatLivre?->id]);

            return response()->json([
                'message' => 'BC transformé en Bon de Livraison avec succès',
                'id'      => $bl->id,
                'numero'  => $bl->numero
            ]);
        });
    }

    /**
     * Transforme un BL en Facture.
     */
    public function blToFacture(Request $request, BonLivraison $bl): JsonResponse
    {
        return DB::transaction(function () use ($request, $bl) {
            $tenantId = $request->get('current_tenant')->id;

            // Pour récupérer les prix (puisqu'ils ne sont pas sur le BL), on remonte au BC ou au Devis
            $source = $bl->bonCommande ?: $bl->devis;
            
            if (!$source) {
                return response()->json(['message' => 'Impossible de trouver les tarifs source pour ce BL.'], 422);
            }

            $facture = Facture::create([
                'tenant_id'              => $tenantId,
                'numero'                 => $this->numerotation->generer($tenantId, 'FACTURE'),
                'date_facture'           => now(),
                'date_echeance'          => now()->addDays(30),
                'client_id'              => $bl->client_id,
                'devis_id'               => $bl->devis_id,
                'projet_id'              => $bl->projet_id,
                'bon_commande_client_id' => $bl->bon_commande_client_id,
                'total_ht'               => $source->total_ht,
                'total_tva'              => $source->total_tva,
                'total_ttc'              => $source->total_ttc,
                'total_remise'           => $source->total_remise,
                'devise_id'              => $source->devise_id,
                'taux_change_document'   => $source->taux_change_document ?? 1.0,
                'observations'           => $bl->observations ?: $source->observations,
                'condition_reglement_id' => $source->condition_reglement_id ?? null,
                'mode_reglement_id'      => $source->mode_reglement_id ?? null,
                'created_by'             => auth()->id() ?? $bl->created_by,
            ]);

            foreach ($source->lignes as $ligne) {
                LigneFacture::create([
                    'tenant_id'       => $tenantId,
                    'facture_id'      => $facture->id,
                    'produit_id'      => $ligne->produit_id,
                    'designation'     => $ligne->designation,
                    'description'     => $ligne->description,
                    'quantite'        => $ligne->quantite,
                    'unite'           => $ligne->unite,
                    'prix_unitaire'   => $ligne->prix_unitaire,
                    'taux_tva'        => $ligne->taux_tva,
                    'remise_pourcent' => $ligne->remise_pourcent,
                    'remise_montant'  => $ligne->remise_montant,
                    'montant_ht'      => $ligne->montant_ht,
                    'montant_tva'     => $ligne->montant_tva,
                    'montant_ttc'     => $ligne->montant_ttc,
                    'ordre'           => $ligne->ordre,
                ]);
            }

            $facture->recalculerTotaux();

            $bl->update(['facture_id' => $facture->id, 'statut' => 'livre']);
            $etatFactureBcc = EtatDocument::where('type_document', 'bcc')->where('code', 'FAC')->first();
            $etatAccDevis = EtatDocument::where('type_document', 'devis')->where('code', 'ACC')->first();

            if ($bl->bonCommande) $bl->bonCommande->update(['est_facture' => true, 'etat_id' => $etatFactureBcc?->id]);
            if ($bl->devis) $bl->devis->update(['est_facture' => true, 'etat_id' => $etatAccDevis?->id]);

            return response()->json([
                'message' => 'Bon de Livraison facturé avec succès',
                'id'      => $facture->id,
                'numero'  => $facture->numero
            ]);
        });
    }

    /**
     * Génère un BL directement à partir d'une Facture de Vente.
     * Cas d'usage : vente directe / comptoir, sans passer par Devis ni BC.
     * Décrémente le stock automatiquement.
     */
    public function factureToBL(Request $request, Facture $facture): JsonResponse
    {
        // Vérifier qu'un BL n'a pas déjà été généré pour cette facture
        $existingBL = BonLivraison::where('facture_id', $facture->id)->first();
        if ($existingBL) {
            return response()->json([
                'message' => 'Un Bon de Livraison existe déjà pour cette facture.',
                'id'      => $existingBL->id,
                'numero'  => $existingBL->numero,
            ], 422);
        }

        return DB::transaction(function () use ($request, $facture) {
            $tenantId = $request->get('current_tenant')->id;

            // 1. Créer le BL
            $bl = BonLivraison::create([
                'tenant_id'      => $tenantId,
                'numero'         => $this->numerotation->generer($tenantId, 'BL'),
                'date_livraison' => now(),
                'client_id'      => $facture->client_id,
                'projet_id'      => $facture->projet_id,
                'facture_id'     => $facture->id,
                'bon_commande_client_id' => $facture->bon_commande_client_id,
                'devis_id'       => $facture->devis_id,
                'entrepot_id'    => $request->input('entrepot_id'),
                'statut'         => 'valide',
                'total_ht'       => $facture->total_ht,
                'total_tva'      => $facture->total_tva,
                'total_ttc'      => $facture->total_ttc,
                'total_remise'   => $facture->total_remise,
                'devise_id'      => $facture->devise_id,
                'taux_change_document' => $facture->taux_change_document ?? 1.0,
                'observations'   => $facture->observations,
                'created_by'     => auth()->id(),
            ]);

            // 2. Copier les lignes + mouvement de stock
            foreach ($facture->lignes as $ligne) {
                LigneBonLivraison::create([
                    'tenant_id'        => $tenantId,
                    'bon_livraison_id' => $bl->id,
                    'produit_id'       => $ligne->produit_id,
                    'designation'      => $ligne->designation,
                    'quantite_prevue'  => $ligne->quantite,
                    'quantite_livree'  => $ligne->quantite,
                    'prix_unitaire'    => $ligne->prix_unitaire,
                    'taux_tva'         => $ligne->taux_tva,
                    'montant_ht'       => $ligne->montant_ht,
                    'montant_tva'      => $ligne->montant_tva,
                    'montant_ttc'      => $ligne->montant_ttc,
                    'ordre'            => $ligne->ordre,
                ]);

                // Sortie de stock si c'est un produit physique
                if ($ligne->produit_id) {
                    \Log::info("WFV: bcToBL movement", ['entrepot' => $request->input('entrepot_id'), 'produit' => $ligne->produit_id]);
                    $this->stockService->enregistrerMouvement(
                        $ligne->produit_id,
                        $ligne->quantite,
                        'sortie_vente',
                        'BL',
                        $bl->id,
                        auth()->id(),
                        $tenantId,
                        $request->input('entrepot_id')
                    );
                }
            }

            return response()->json([
                'message' => 'Bon de Livraison généré et stock mis à jour.',
                'id'      => $bl->id,
                'numero'  => $bl->numero,
            ]);
        });
    }
}