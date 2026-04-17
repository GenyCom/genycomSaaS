<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Models\LigneDevis;
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
            ->orderBy($request->sort_by ?? 'date_devis', $request->sort_dir ?? 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'projet_id'              => 'nullable|exists:projets,id',
            'date_devis'             => 'required|date',
            'date_validite'          => 'nullable|date',
            'condition_reglement_id' => 'nullable|exists:condition_reglement,id',
            'observations'           => 'nullable|string',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.produit_id'    => 'nullable|exists:produits,id',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.quantite'      => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva'      => 'required|numeric|min:0',
            'lignes.*.remise_pourcent' => 'nullable|numeric|min:0|max:100',
            'lignes.*.remise_montant'  => 'nullable|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $data['numero'] = $this->numerotation->generer($tenantId, 'DEVIS', new \DateTime($data['date_devis']));
        $data['tenant_id'] = $tenantId;

        $devis = Devis::create(collect($data)->except('lignes')->toArray());

        foreach ($data['lignes'] as $index => $ligneData) {
            $ligneData['devis_id'] = $devis->id;
            $ligneData['ordre'] = $index + 1;
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

    public function destroy(Devis $devi): JsonResponse
    {
        $devi->delete();
        return response()->json(['message' => 'Devis supprimé']);
    }
}
