<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BonCommandeClient;
use App\Models\Devise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BonCommandeClientController extends Controller
{
    public function __construct(private \App\Services\NumerotationService $numerotation) {}

    public function index(Request $request): JsonResponse
    {
        $query = BonCommandeClient::with(['client', 'etat'])
            ->orderBy('created_at', 'desc');
        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'              => 'required|integer',
            'projet_id'              => 'nullable|integer',
            'date_commande'          => 'required|date',
            'date_livraison_prevue'  => 'nullable|date',
            'observations'           => 'nullable|string',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.produit_id'    => 'nullable|integer',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.quantite'      => 'required|numeric|min:0.01',
            'lignes.*.unite'         => 'nullable|string',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva'      => 'required|numeric|min:0',
            'devise_id'               => 'nullable|integer',
            'taux_change_document'    => 'nullable|numeric',
        ]);

        $tenantId = $request->get('current_tenant')->id;
        $data['numero'] = $this->numerotation->generer($tenantId, 'BCC', new \DateTime($data['date_commande']));
        $data['tenant_id'] = $tenantId;

        // Gestion Devise & Taux
        if (empty($data['devise_id'])) {
            $devise = Devise::where('tenant_id', $tenantId)->where('is_principale', true)->first();
            $data['devise_id'] = $devise?->id;
            $data['taux_change_document'] = 1.0;
        } else {
            if (empty($data['taux_change_document'])) {
                $devise = Devise::find($data['devise_id']);
                $data['taux_change_document'] = $devise?->taux_change ?? 1.0;
            }
        }

        $bcc = BonCommandeClient::create(collect($data)->except('lignes')->toArray());

        foreach ($data['lignes'] as $index => $ligneData) {
            $ligneData['bon_commande_client_id'] = $bcc->id;
            $ligneData['tenant_id'] = $tenantId;
            $ligneData['ordre'] = $index + 1;
            
            // Calculer les montants de la ligne
            $qty = (float)($ligneData['quantite'] ?? 0);
            $pu  = (float)($ligneData['prix_unitaire'] ?? 0);
            $tva = (float)($ligneData['taux_tva'] ?? 0);
            $remise_p = (float)($ligneData['remise_pourcent'] ?? 0);

            $base_ht = $qty * $pu;
            $remise_m = $base_ht * ($remise_p / 100);
            $net_ht = $base_ht - $remise_m;
            $mt_tva = $net_ht * ($tva / 100);

            $ligneData['remise_montant'] = $remise_m;
            $ligneData['montant_ht']     = $net_ht;
            $ligneData['montant_tva']    = $mt_tva;
            $ligneData['montant_ttc']    = $net_ht + $mt_tva;
            
            \App\Models\LigneBonCommandeClient::create($ligneData);
        }

        $bcc->recalculerTotaux();

        return response()->json($bcc->load('lignes', 'client', 'etat'), 201);
    }

    public function show($id): JsonResponse
    {
        $bcc = BonCommandeClient::with(['lignes.produit', 'client', 'devis', 'bonsLivraison'])->findOrFail($id);
        return response()->json($bcc);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $bcc = BonCommandeClient::findOrFail($id);
        
        $data = $request->validate([
            'client_id'              => 'required|integer',
            'projet_id'              => 'nullable|integer',
            'date_commande'          => 'required|date',
            'date_livraison_prevue'  => 'nullable|date',
            'observations'           => 'nullable|string',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.produit_id'    => 'nullable|integer',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.quantite'      => 'required|numeric|min:0.01',
            'lignes.*.unite'         => 'nullable|string',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva'      => 'required|numeric|min:0',
            'devise_id'               => 'nullable|integer',
            'taux_change_document'    => 'nullable|numeric',
        ]);

        // Gestion Devise & Taux (Update)
        if (!empty($data['devise_id']) && empty($data['taux_change_document'])) {
            $devise = Devise::find($data['devise_id']);
            $data['taux_change_document'] = $devise?->taux_change ?? 1.0;
        }

        $bcc->update(collect($data)->except('lignes')->toArray());

        // Sync lines
        $bcc->lignes()->delete();
        $tenantId = $request->get('current_tenant')->id;

        foreach ($data['lignes'] as $index => $ligneData) {
            $ligneData['bon_commande_client_id'] = $bcc->id;
            $ligneData['tenant_id'] = $tenantId;
            $ligneData['ordre'] = $index + 1;
            
            // Calculer les montants de la ligne
            $qty = (float)($ligneData['quantite'] ?? 0);
            $pu  = (float)($ligneData['prix_unitaire'] ?? 0);
            $tva = (float)($ligneData['taux_tva'] ?? 0);
            $remise_p = (float)($ligneData['remise_pourcent'] ?? 0);

            $base_ht = $qty * $pu;
            $remise_m = $base_ht * ($remise_p / 100);
            $net_ht = $base_ht - $remise_m;
            $mt_tva = $net_ht * ($tva / 100);

            $ligneData['remise_montant'] = $remise_m;
            $ligneData['montant_ht']     = $net_ht;
            $ligneData['montant_tva']    = $mt_tva;
            $ligneData['montant_ttc']    = $net_ht + $mt_tva;
            
            \App\Models\LigneBonCommandeClient::create($ligneData);
        }

        $bcc->recalculerTotaux();

        return response()->json($bcc->load('lignes', 'client', 'etat'));
    }

    public function destroy($id): JsonResponse
    {
        $bcc = BonCommandeClient::withCount(['bonsLivraison', 'factures'])->findOrFail($id);
        
        if ($bcc->bons_livraison_count > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer cette commande car des bons de livraison y sont rattachés.'
            ], 422);
        }

        if ($bcc->factures_count > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer cette commande car elle a déjà été facturée.'
            ], 422);
        }

        $bcc->delete();
        return response()->json(['message' => 'Bon de commande supprimé avec succès']);
    }
}
