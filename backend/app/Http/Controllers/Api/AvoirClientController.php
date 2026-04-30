<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvoirClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Services\StockService;
use App\Services\NumerotationService;
use App\Models\LigneAvoirClient;

class AvoirClientController extends Controller
{
    public function __construct(
        private StockService $stockService,
        private NumerotationService $numerotation
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = AvoirClient::with(['client', 'facture'])
            ->when($request->search, function($q, $v) {
                $q->where(function($sq) use ($v) {
                    $sq->where('numero', 'like', "%{$v}%")
                       ->orWhere('motif', 'like', "%{$v}%")
                       ->orWhereHas('client', fn($cq) => $cq->where('nom', 'like', "%{$v}%")->orWhere('societe', 'like', "%{$v}%"))
                       ->orWhereHas('facture', fn($fq) => $fq->where('numero', 'like', "%{$v}%"));
                });
            })
            ->orderBy('date_avoir', 'desc')
            ->orderBy('id', 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'    => 'required|integer',
            'facture_id'   => 'nullable|integer',
            'date_avoir'   => 'required|date',
            'motif'        => 'required|string',
            'observations' => 'nullable|string',
            'lignes'       => 'required|array|min:1',
            'lignes.*.produit_id' => 'nullable|integer',
            'lignes.*.designation' => 'required|string',
            'lignes.*.quantite'    => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva'      => 'required|numeric|min:0',
        ]);

        $tenant = $request->get('current_tenant');
        $tenantId = $tenant?->id;

        if (!$tenantId) {
             return response()->json(['message' => 'Tenant non identifié.'], 403);
        }

        return \DB::transaction(function() use ($data, $tenantId) {
            $etatBrouillon = \App\Models\EtatDocument::where('tenant_id', $tenantId)
                ->where('type_document', 'avoir_client')->where('code', 'BRL')->first();

            $avoir = AvoirClient::create([
                'tenant_id' => $tenantId,
                'numero' => $this->numerotation->generer($tenantId, 'AVOIR_CLIENT'),
                'client_id' => $data['client_id'],
                'facture_id' => $data['facture_id'] ?? null,
                'date_avoir' => $data['date_avoir'],
                'motif' => $data['motif'],
                'observations' => $data['observations'] ?? null,
                'etat_id' => $etatBrouillon?->id,
                'created_by' => auth()->id()
            ]);

            $totalHt = 0;
            $totalTva = 0;

            foreach ($data['lignes'] as $ligneData) {
                $ht = $ligneData['quantite'] * $ligneData['prix_unitaire'];
                $tva = $ht * ($ligneData['taux_tva'] / 100);

                LigneAvoirClient::create([
                    'avoir_id' => $avoir->id,
                    'produit_id' => $ligneData['produit_id'] ?? null,
                    'designation' => $ligneData['designation'],
                    'quantite' => $ligneData['quantite'],
                    'prix_unitaire' => $ligneData['prix_unitaire'],
                    'taux_tva' => $ligneData['taux_tva'],
                    'montant_ht' => $ht,
                    'montant_tva' => $tva,
                    'montant_ttc' => $ht + $tva
                ]);

                $totalHt += $ht;
                $totalTva += $tva;
            }

            $avoir->update([
                'total_ht' => $totalHt,
                'total_tva' => $totalTva,
                'total_ttc' => $totalHt + $totalTva
            ]);

            return response()->json($avoir->load('lignes'), 201);
        });
    }

    public function show($id): JsonResponse
    {
        $avoir = AvoirClient::with(['client', 'facture', 'lignes.produit'])
            ->findOrFail($id);

        return response()->json($avoir);
    }

    public function valider($id): JsonResponse
    {
        $avoir = AvoirClient::findOrFail($id);

        if ($avoir->etat_id === 2) { // Supposons 2 = Validé/Ouvert
            return response()->json(['message' => 'Cet avoir est déjà validé.'], 422);
        }

        \DB::transaction(function() use ($avoir) {
            $tenantId = auth()->user()->tenant_id;
            $etatValide = \App\Models\EtatDocument::where('tenant_id', $tenantId)
                ->where('type_document', 'avoir_client')->where('code', 'VAL')->first();
            
            $avoir->update(['etat_id' => $etatValide?->id]); // Validé
            $this->stockService->validerAvoirClient($avoir->id, auth()->id());
        });

        return response()->json(['message' => 'Avoir validé et stock mis à jour.', 'avoir' => $avoir->fresh()]);
    }
}
