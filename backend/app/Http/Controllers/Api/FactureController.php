<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\LigneFacture;
use App\Services\FacturationService;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FactureController extends Controller
{
    public function __construct(
        private FacturationService $facturation,
        private NumerotationService $numerotation,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Facture::with(['client:id,societe,code_client', 'etat'])
            ->when($request->client_id, fn($q, $v) => $q->where('client_id', $v))
            ->when($request->etat_id, fn($q, $v) => $q->where('etat_id', $v))
            ->when($request->est_reglee !== null, fn($q) => $q->where('est_reglee', $request->boolean('est_reglee')))
            ->when($request->date_debut, fn($q, $v) => $q->where('date_facture', '>=', $v))
            ->when($request->date_fin, fn($q, $v) => $q->where('date_facture', '<=', $v))
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('numero', 'like', "%{$v}%")
                   ->orWhereHas('client', fn($cq) => $cq->where('societe', 'like', "%{$v}%"));
            }))
            ->orderBy($request->sort_by ?? 'date_facture', $request->sort_dir ?? 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'projet_id'              => 'nullable|exists:projets,id',
            'date_facture'           => 'required|date',
            'date_echeance'          => 'nullable|date',
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
        $data['numero'] = $this->numerotation->generer($tenantId, 'FACTURE', new \DateTime($data['date_facture']));
        $data['tenant_id'] = $tenantId;
        
        $facture = Facture::create(collect($data)->except('lignes')->toArray());

        foreach ($data['lignes'] as $index => $ligneData) {
            $ligneData['facture_id'] = $facture->id;
            $ligneData['ordre'] = $index + 1;
            $ligneData['source_type'] = 'saisie_manuelle';
            LigneFacture::create($ligneData);
        }

        $facture->recalculerTotaux();

        return response()->json($facture->fresh('lignes', 'client', 'etat'), 201);
    }

    public function show(Facture $facture): JsonResponse
    {
        return response()->json(
            $facture->load(['lignes.produit:id,reference,designation', 'client', 'etat', 'reglements.modeReglement', 'conditionReglement', 'createur:id,nom,prenom'])
        );
    }

    public function update(Request $request, Facture $facture): JsonResponse
    {
        $data = $request->validate([
            'client_id'              => 'sometimes|exists:clients,id',
            'date_facture'           => 'sometimes|date',
            'date_echeance'          => 'nullable|date',
            'condition_reglement_id' => 'nullable|exists:condition_reglement,id',
            'observations'           => 'nullable|string',
        ]);

        $facture->update($data);
        return response()->json($facture->fresh('lignes', 'client', 'etat'));
    }

    public function destroy(Facture $facture): JsonResponse
    {
        $facture->delete();
        return response()->json(['message' => 'Facture supprimée']);
    }

    /**
     * POST /api/factures/{facture}/valider
     */
    public function valider(Facture $facture): JsonResponse
    {
        $facture = $this->facturation->valider($facture);
        return response()->json($facture->load('etat'));
    }

    /**
     * POST /api/factures/{facture}/reglement
     */
    public function reglement(Request $request, Facture $facture): JsonResponse
    {
        $data = $request->validate([
            'date_reglement'    => 'required|date',
            'montant'           => 'required|numeric|min:0.01',
            'mode_reglement_id' => 'nullable|exists:mode_reglement,id',
            'numero_cheque'     => 'nullable|string',
            'banque'            => 'nullable|string',
            'reference_virement'=> 'nullable|string',
            'observations'      => 'nullable|string',
        ]);

        $this->facturation->enregistrerReglement($facture, $data);
        return response()->json($facture->fresh('lignes', 'etat', 'reglements'));
    }
}
