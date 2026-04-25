<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvoirFournisseur;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Services\StockService;
use App\Services\NumerotationService;
use App\Models\LigneAvoirFournisseur;

class AvoirFournisseurController extends Controller
{
    public function __construct(
        private StockService $stockService,
        private NumerotationService $numerotation
    ) {}

    public function index(Request $request): JsonResponse
    {
        // CORRECTION : On charge 'facture' au lieu de 'commande'
        $query = AvoirFournisseur::with(['fournisseur', 'facture'])
            ->when($request->search, function($q, $v) {
                $q->where(function($sq) use ($v) {
                    $sq->where('numero', 'like', "%{$v}%")
                       ->orWhere('motif', 'like', "%{$v}%")
                       ->orWhereHas('fournisseur', fn($cq) => $cq->where('nom', 'like', "%{$v}%")->orWhere('societe', 'like', "%{$v}%"))
                       // CORRECTION : Recherche sur le numéro de la facture liée
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
            'fournisseur_id'   => 'required|integer',
            'facture_achat_id' => 'nullable|integer',
            'date_avoir'       => 'required|date',
            'motif'            => 'required|string',
            'observations'     => 'nullable|string',
            'lignes'           => 'required|array|min:1',
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
            $avoir = AvoirFournisseur::create([
                'tenant_id' => $tenantId,
                'numero' => $this->numerotation->generer($tenantId, 'AVOIR_ACHAT'),
                'fournisseur_id' => $data['fournisseur_id'],
                'facture_achat_id' => $data['facture_achat_id'] ?? null,
                'date_avoir' => $data['date_avoir'],
                'motif' => $data['motif'],
                'observations' => $data['observations'] ?? null,
                'statut' => 'brouillon',
                'created_by' => auth()->id()
            ]);

            $totalHt = 0;
            $totalTva = 0;

            foreach ($data['lignes'] as $ligneData) {
                $ht = $ligneData['quantite'] * $ligneData['prix_unitaire'];
                $tva = $ht * ($ligneData['taux_tva'] / 100);
                
                LigneAvoirFournisseur::create([
                    'tenant_id' => $tenantId,
                    'avoir_achat_id' => $avoir->id,
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
                'montant_ht' => $totalHt,
                'montant_tva' => $totalTva,
                'montant_ttc' => $totalHt + $totalTva
            ]);

            return response()->json($avoir->load('lignes'), 201);
        });
    }

    public function show($id): JsonResponse
    {
        // CORRECTION : On charge 'facture' au lieu de 'commande'
        $avoir = AvoirFournisseur::with(['fournisseur', 'facture', 'lignes.produit'])
            ->findOrFail($id);

        return response()->json($avoir);
    }

    public function valider($id): JsonResponse
    {
        $avoir = AvoirFournisseur::findOrFail($id);

        if ($avoir->statut === 'valide') {
            return response()->json(['message' => 'Cet avoir est déjà validé.'], 422);
        }

        \DB::transaction(function() use ($avoir) {
            $avoir->update(['statut' => 'valide']);
            $this->stockService->validerAvoirFournisseur($avoir->id, auth()->id());
        });

        return response()->json(['message' => 'Avoir fournisseur validé et stock décrémenté.', 'avoir' => $avoir->fresh()]);
    }
}