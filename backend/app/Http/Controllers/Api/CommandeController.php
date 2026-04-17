<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\LigneCommande;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommandeController extends Controller
{
    public function __construct(private NumerotationService $numerotation) {}

    public function index(Request $request): JsonResponse
    {
        $query = Commande::with(['fournisseur:id,societe,code_fournisseur', 'etat'])
            ->when($request->fournisseur_id, fn($q, $v) => $q->where('fournisseur_id', $v))
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('numero', 'like', "%{$v}%")
                   ->orWhereHas('fournisseur', fn($cq) => $cq->where('societe', 'like', "%{$v}%"));
            }))
            ->orderBy($request->sort_by ?? 'date_commande', $request->sort_dir ?? 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fournisseur_id'         => 'required|exists:fournisseurs,id',
            'projet_id'              => 'nullable|exists:projets,id',
            'date_commande'          => 'required|date',
            'date_livraison_prevue'  => 'nullable|date',
            'condition_reglement_id' => 'nullable|exists:condition_reglement,id',
            'observations'           => 'nullable|string',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.produit_id'    => 'nullable|exists:produits,id',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.quantite'      => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva'      => 'required|numeric|min:0',
            'lignes.*.remise_pourcent' => 'nullable|numeric|min:0|max:100',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $data['numero'] = $this->numerotation->generer($tenantId, 'COMMANDE_FOURNISSEUR', new \DateTime($data['date_commande']));
        $data['tenant_id'] = $tenantId;

        $commande = Commande::create(collect($data)->except('lignes')->toArray());

        foreach ($data['lignes'] as $index => $ligneData) {
            $ligneData['commande_id'] = $commande->id;
            $ligneData['ordre'] = $index + 1;
            LigneCommande::create($ligneData);
        }

        $commande->recalculerTotaux();

        return response()->json($commande->fresh('lignes', 'fournisseur', 'etat'), 201);
    }

    public function show(Commande $commande): JsonResponse
    {
        return response()->json(
            $commande->load(['lignes.produit:id,reference,designation', 'fournisseur', 'etat', 'createur:id,nom,prenom'])
        );
    }
}
