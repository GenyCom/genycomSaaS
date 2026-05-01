<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BonCommandeFournisseur;
use App\Models\LigneBonCommandeFournisseur;
use App\Models\Devise;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommandeController extends Controller
{
    public function __construct(private NumerotationService $numerotation) {}

    public function index(Request $request): JsonResponse
    {
        $query = BonCommandeFournisseur::with(['fournisseur:id,societe,code_fournisseur', 'etat'])
            ->when($request->fournisseur_id, fn($q, $v) => $q->where('fournisseur_id', $v))
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('numero', 'like', "%{$v}%")
                   ->orWhereHas('fournisseur', fn($cq) => $cq->where('societe', 'like', "%{$v}%"));
            }))
            ->orderBy($request->sort_by ?? 'id', $request->sort_dir ?? 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fournisseur_id'         => 'required|integer',
            'projet_id'              => 'nullable|integer',
            'date_commande'          => 'required|date',
            'date_livraison_prevue'  => 'nullable|date',
            'condition_reglement_id' => 'nullable|integer',
            'observations'           => 'nullable|string',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.produit_id'    => 'nullable|integer',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.quantite'      => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva'      => 'required|numeric|min:0',
            'lignes.*.remise_pourcent' => 'nullable|numeric|min:0|max:100',
        ]);

        $tenantId = $request->get('current_tenant')->id;
        $data['numero'] = $this->numerotation->generer($tenantId, 'COMMANDE', new \DateTime($data['date_commande']));
        $data['tenant_id'] = $tenantId;

        // Etat par défaut
        $etatBrouillon = \App\Models\EtatDocument::where('type_document', 'bcf')
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

        $commande = BonCommandeFournisseur::create(collect($data)->except('lignes')->toArray());

        foreach ($data['lignes'] as $index => $ligneData) {
            $ligneData['bcf_id'] = $commande->id;
            $ligneData['ordre'] = $index + 1;
            LigneBonCommandeFournisseur::create($ligneData);
        }

        $commande->recalculerTotaux();

        return response()->json($commande->fresh('lignes', 'fournisseur', 'etat'), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'fournisseur_id'         => 'required|integer',
            'projet_id'              => 'nullable|integer',
            'date_commande'          => 'required|date',
            'date_livraison_prevue'  => 'nullable|date',
            'condition_reglement_id' => 'nullable|integer',
            'observations'           => 'nullable|string',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.produit_id'    => 'nullable|integer',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.quantite'      => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva'      => 'required|numeric|min:0',
            'lignes.*.remise_pourcent' => 'nullable|numeric|min:0|max:100',
        ]);

        $commande = BonCommandeFournisseur::findOrFail($id);

        // Gestion Devise & Taux (Update)
        if (!empty($data['devise_id']) && empty($data['taux_change_document'])) {
            $devise = Devise::find($data['devise_id']);
            $data['taux_change_document'] = $devise?->taux_change ?? 1.0;
        }

        $commande->update(collect($data)->except('lignes')->toArray());

        // Sync lines
        $commande->lignes()->delete();
        $tenantId = $request->get('current_tenant')->id;

        foreach ($data['lignes'] as $index => $ligneData) {
            $ligneData['bcf_id'] = $commande->id;
            $ligneData['ordre'] = $index + 1;
            LigneBonCommandeFournisseur::create($ligneData);
        }

        $commande->recalculerTotaux();

        return response()->json($commande->fresh('lignes', 'fournisseur', 'etat'));
    }

    public function show(BonCommandeFournisseur $commande): JsonResponse
    {
        return response()->json(
            $commande->load(['lignes.produit:id,reference,designation', 'fournisseur', 'etat', 'createur:id,nom,prenom'])
        );
    }

    public function destroy($id): JsonResponse
    {
        $commande = BonCommandeFournisseur::withCount(['bonsReception', 'dettes'])->findOrFail($id);
        
        if ($commande->bons_reception_count > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer cette commande car des réceptions y sont rattachées.'
            ], 422);
        }

        if ($commande->dettes_count > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer cette commande car elle a déjà été facturée.'
            ], 422);
        }

        $commande->delete();
        return response()->json(['message' => 'Commande fournisseur supprimée avec succès.']);
    }
}
