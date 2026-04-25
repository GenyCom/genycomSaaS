<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Models\LigneDevis;
use App\Models\Devise;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DevisController extends Controller
{
    public function __construct(private NumerotationService $numerotation) {}

    public function index(Request $request): JsonResponse
    {
        $query = Devis::with(['client:id,societe,code_client', 'etat'])
            ->when($request->client_id, fn($q, $v) => $q->where('client_id', $v))
            ->when($request->etat_id, fn($q, $v) => $q->where('etat_id', $v))
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('numero', 'like', "%{$v}%")
                   ->orWhereHas('client', fn($cq) => $cq->where('societe', 'like', "%{$v}%"));
            }))
            ->orderBy($request->sort_by ?? 'date_devis', $request->sort_dir ?? 'desc')
            ->orderBy('id', 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'              => 'required|integer',
            'projet_id'              => 'nullable|integer',
            'date_devis'             => 'required|date',
            'date_validite'          => 'nullable|date',
            'condition_reglement_id' => 'nullable|integer',
            'observations'           => 'nullable|string',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.produit_id'    => 'nullable',
            'lignes.*.designation'   => 'required|string',
            'lignes.*.unite'         => 'nullable|string|max:50',
            'lignes.*.quantite'      => 'required|numeric',
            'lignes.*.prix_unitaire' => 'required|numeric',
            'lignes.*.taux_tva'      => 'required|numeric',
            'lignes.*.remise_pourcent' => 'nullable|numeric',
            'lignes.*.remise_montant'  => 'nullable|numeric',
        ]);

        $tenantId = $request->get('current_tenant')->id;
        $data['numero'] = $this->numerotation->generer($tenantId, 'DEVIS', new \DateTime($data['date_devis']));
        $data['tenant_id'] = $tenantId;

        // Nettoyage IDs
        if (empty($data['projet_id'])) $data['projet_id'] = null;

        // Etat par défaut
        $etatBrouillon = \App\Models\EtatDocument::where('tenant_id', $tenantId)
            ->where('type_document', 'devis')
            ->where('code', 'BROUILLON')
            ->first();
        if ($etatBrouillon) {
            $data['etat_id'] = $etatBrouillon->id;
        }

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

        $devis = Devis::create(collect($data)->except('lignes')->toArray());

        foreach ($data['lignes'] as $index => $ligneData) {
            $ligneData['devis_id'] = $devis->id;
            $ligneData['ordre'] = $index + 1;
            $ligneData['tenant_id'] = $tenantId;

            if (empty($ligneData['produit_id'])) $ligneData['produit_id'] = null;

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

            LigneDevis::create($ligneData);
        }

        $devis->recalculerTotaux();

        return response()->json($devis->fresh('lignes', 'client', 'etat'), 201);
    }

    public function show(Devis $devi): JsonResponse
    {
        // Parameter automatically resolved via route-model binding. Due to plural 'devis', it binds to $devi or we explicit setup
        return response()->json(
            $devi->load(['lignes.produit:id,reference,designation', 'client', 'etat', 'createur:id,nom,prenom'])
        );
    }

    public function update(Request $request, Devis $devi): JsonResponse
    {
        $data = $request->validate([
            'client_id'              => 'required|integer',
            'projet_id'              => 'nullable|integer',
            'date_devis'             => 'required|date',
            'date_validite'          => 'nullable|date',
            'condition_reglement_id' => 'nullable|integer',
            'observations'           => 'nullable|string',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.produit_id'    => 'nullable',
            'lignes.*.designation'   => 'required|string',
            'lignes.*.unite'         => 'nullable|string|max:50',
            'lignes.*.quantite'      => 'required|numeric',
            'lignes.*.prix_unitaire' => 'required|numeric',
            'lignes.*.taux_tva'      => 'required|numeric',
            'lignes.*.remise_pourcent' => 'nullable|numeric',
            'lignes.*.remise_montant'  => 'nullable|numeric',
        ]);

        // Nettoyage des IDs vides...
        if (empty($data['projet_id'])) $data['projet_id'] = null;

        // Gestion Devise & Taux (Update)
        if (!empty($data['devise_id']) && empty($data['taux_change_document'])) {
            $devise = Devise::find($data['devise_id']);
            $data['taux_change_document'] = $devise?->taux_change ?? 1.0;
        }

        $devi->update(collect($data)->except('lignes')->toArray());

        $devi->lignes()->delete();

        foreach ($data['lignes'] as $index => $ligneData) {
            $ligneData['devis_id'] = $devi->id;
            $ligneData['ordre'] = $index + 1;
            $ligneData['tenant_id'] = $devi->tenant_id;
            
            if (empty($ligneData['produit_id'])) $ligneData['produit_id'] = null;
            
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

            LigneDevis::create($ligneData);
        }

        $devi->recalculerTotaux();

        return response()->json($devi->fresh('lignes', 'client', 'etat'));
    }

    public function destroy(Devis $devi): JsonResponse
    {
        // Vérifier si transformé en commande
        if ($devi->bonsCommande()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer ce devis car il a déjà été transformé en bon de commande.'
            ], 422);
        }

        // Vérifier si facturé directement
        if ($devi->factures()->exists()) {
             return response()->json([
                'message' => 'Impossible de supprimer ce devis car il est lié à une facture.'
            ], 422);
        }

        $devi->delete();
        return response()->json(['message' => 'Devis supprimé avec succès']);
    }
}
