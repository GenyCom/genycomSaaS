<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\MouvementStock;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    protected $stockService;

    public function __construct(\App\Services\StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request): JsonResponse
    {
        // 1. Suppression de withoutGlobalScopes() pour sécuriser l'isolation du Tenant
        $query = Stock::with(['produit:id,reference,designation,is_service,seuil_alerte', 'entrepot']) // Ajout de seuil_alerte pour l'état IHM
            ->when($request->entrepot_id, fn($q, $v) => $q->where('entrepot_id', $v))
            ->when($request->search, fn($q, $v) => $q->whereHas('produit', function($sq) use ($v) {
                $sq->where('designation', 'like', "%{$v}%")
                   ->orWhere('reference', 'like', "%{$v}%");
            }))
            ->orderBy($request->sort_by ?? 'quantite', $request->sort_dir ?? 'desc');

        return response()->json($query->paginate($request->per_page ?? 50));
    }

    public function adjust(Request $request): JsonResponse
    {
        $data = $request->validate([
            'produit_id' => 'required|exists:tenant.produits,id',
            'entrepot_id' => 'nullable|exists:tenant.entrepots,id',
            'quantite' => 'required|numeric|min:0.01',
            'type' => 'required|in:ajustement_positif,ajustement_negatif',
            'motif' => 'nullable|string|max:255'
        ]);

        $tenantId = $request->get('current_tenant')->id;

        $this->stockService->ajusterManual(
            $data['produit_id'],
            $data['entrepot_id'] ?? null,
            $data['quantite'],
            $data['type'],
            auth()->id(),
            $tenantId
        );

        return response()->json(['message' => 'Stock ajusté avec succès']);
    }

    public function transfer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'produit_id' => 'required|exists:tenant.produits,id',
            'entrepot_source_id' => 'required|exists:tenant.entrepots,id',
            'entrepot_dest_id' => 'required|exists:tenant.entrepots,id|different:entrepot_source_id',
            'quantite' => 'required|numeric|min:0.01',
            'motif' => 'nullable|string|max:255'
        ]);

        try {
            $tenantId = $request->get('current_tenant')->id;
            
            // Logique de transfert (directement via service pour transaction atomique)
            DB::transaction(function() use ($data, $tenantId) {
                $this->stockService->enregistrerMouvement(
                    $data['produit_id'], $data['quantite'], 'transfert_out', 'TRANSFER', null, auth()->id(), $tenantId, $data['entrepot_source_id']
                );
                $this->stockService->enregistrerMouvement(
                    $data['produit_id'], $data['quantite'], 'transfert_in', 'TRANSFER', null, auth()->id(), $tenantId, $data['entrepot_dest_id']
                );
            });
            return response()->json(['message' => 'Transfert effectué']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show($id): JsonResponse
    {
        $stock = Stock::with(['produit', 'entrepot'])->findOrFail($id);
        
        $mouvements = MouvementStock::with('auteur:id,nom,prenom')
            ->where('produit_id', $stock->produit_id)
            ->latest('id')
            ->limit(100)
            ->get();

        return response()->json([
            'stock' => $stock,
            'mouvements' => $mouvements
        ]);
    }

    public function getUninitializedProducts(Request $request): JsonResponse
    {
        $produits = Produit::where('is_service', false)
            ->whereDoesntHave('stocks')
            ->orderBy('designation', 'asc')
            ->get(['id', 'reference', 'designation', 'seuil_alerte', 'emplacement_stock', 'stock_actuel', 'created_at']);

        return response()->json([
            'total' => $produits->count(),
            'data'  => $produits
        ]);
    }

    public function initialize(Request $request): JsonResponse
    {
        $data = $request->validate([
            'entrepot_id' => 'required|exists:tenant.entrepots,id',
            'items' => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:tenant.produits,id',
            'items.*.quantite' => 'required|numeric|min:0',
            'items.*.seuil_alerte' => 'nullable|numeric|min:0',
            'items.*.stock_min' => 'nullable|numeric|min:0',
            'items.*.stock_max' => 'nullable|numeric|min:0',
            'items.*.emplacement_stock' => 'nullable|string|max:100',
            'motif' => 'nullable|string|max:255',
        ]);

        $tenantId = $request->get('current_tenant')->id;
        $entrepotId = $data['entrepot_id'];
        $userId = auth()->id();

        $count = 0;

        DB::transaction(function () use ($data, $tenantId, $entrepotId, $userId, &$count) {
            foreach ($data['items'] as $item) {
                $produit = Produit::where('id', $item['produit_id'])
                    ->where('tenant_id', $tenantId)
                    ->firstOrFail();

                // Ignorer les services
                if ($produit->is_service) {
                    continue;
                }

                // 1. Mettre à jour métadonnées du produit si spécifiées
                $updateMeta = [];
                if (isset($item['seuil_alerte']) && $item['seuil_alerte'] !== null) {
                    $updateMeta['seuil_alerte'] = $item['seuil_alerte'];
                }
                if (isset($item['stock_min']) && $item['stock_min'] !== null) {
                    $updateMeta['stock_min'] = $item['stock_min'];
                }
                if (isset($item['stock_max']) && $item['stock_max'] !== null) {
                    $updateMeta['stock_max'] = $item['stock_max'];
                }
                if (isset($item['emplacement_stock']) && $item['emplacement_stock'] !== null) {
                    $updateMeta['emplacement_stock'] = $item['emplacement_stock'];
                }
                if (!empty($updateMeta)) {
                    $produit->update($updateMeta);
                }

                // 2. Traiter le niveau de stock pour l'entrepôt sélectionné
                $stockLine = Stock::firstOrCreate(
                    ['produit_id' => $produit->id, 'entrepot_id' => $entrepotId, 'tenant_id' => $tenantId],
                    ['quantite' => 0]
                );

                $currentQty = (float) $stockLine->quantite;
                $targetQty = (float) $item['quantite'];
                $delta = $targetQty - $currentQty;

                if ($delta != 0) {
                    $typeMouvement = $delta > 0 ? 'ajustement_positif' : 'ajustement_negatif';
                    $qtyToRecord = abs($delta);

                    $this->stockService->enregistrerMouvement(
                        $produit->id,
                        $qtyToRecord,
                        $typeMouvement,
                        'INITIALISATION',
                        null,
                        $userId,
                        $tenantId,
                        $entrepotId
                    );
                }

                $count++;
            }
        });

        return response()->json([
            'message' => "Stock initialisé avec succès pour {$count} produit(s)."
        ]);
    }

    public function getFullInitData(Request $request): JsonResponse
    {
        $tenantId = $request->get('current_tenant')->id;
        $defaultEntrepotId = $this->stockService->getDefaultEntrepotId($tenantId);
        $defaultEntrepot = \App\Models\Entrepot::find($defaultEntrepotId);
        $defaultEntrepotNom = $defaultEntrepot ? $defaultEntrepot->nom : 'Dépôt Principal';

        $produits = Produit::where('is_service', false)
            ->with(['stocks.entrepot'])
            ->orderBy('designation', 'asc')
            ->get();

        $items = [];

        foreach ($produits as $produit) {
            if ($produit->stocks && $produit->stocks->count() > 0) {
                foreach ($produit->stocks as $stock) {
                    $items[] = [
                        'produit_id'        => $produit->id,
                        'entrepot_id'       => $stock->entrepot_id,
                        'reference'         => $produit->reference,
                        'designation'       => $produit->designation,
                        'entrepot_nom'      => $stock->entrepot ? $stock->entrepot->nom : 'Dépôt Inconnu',
                        'quantite_actuelle' => (float) $stock->quantite,
                        'quantite'          => 0, // Défaut à 0 comme demandé
                        'seuil_alerte'      => $produit->seuil_alerte ?? 5,
                        'emplacement_stock' => $produit->emplacement_stock ?? '',
                        'selected'          => true,
                    ];
                }
            } else {
                // Aucun stock encore créé pour ce produit -> Associer au dépôt principal par défaut
                $items[] = [
                    'produit_id'        => $produit->id,
                    'entrepot_id'       => $defaultEntrepotId,
                    'reference'         => $produit->reference,
                    'designation'       => $produit->designation,
                    'entrepot_nom'      => $defaultEntrepotNom,
                    'quantite_actuelle' => 0,
                    'quantite'          => 0, // Défaut à 0 comme demandé
                    'seuil_alerte'      => $produit->seuil_alerte ?? 5,
                    'emplacement_stock' => $produit->emplacement_stock ?? '',
                    'selected'          => true,
                ];
            }
        }

        return response()->json([
            'total' => count($items),
            'data'  => $items
        ]);
    }

    public function fullInitialize(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.produit_id'        => 'required|exists:tenant.produits,id',
            'items.*.entrepot_id'       => 'required|exists:tenant.entrepots,id',
            'items.*.quantite'          => 'required|numeric|min:0',
            'items.*.seuil_alerte'      => 'nullable|numeric|min:0',
            'items.*.emplacement_stock' => 'nullable|string|max:100',
            'motif'                     => 'nullable|string|max:255',
        ]);

        $tenantId = $request->get('current_tenant')->id;
        $userId = auth()->id();

        $updatedLinesCount = 0;
        $productsSet = [];

        DB::transaction(function () use ($data, $tenantId, $userId, &$updatedLinesCount, &$productsSet) {
            foreach ($data['items'] as $item) {
                $entrepotId = $item['entrepot_id'];
                $produit = Produit::where('id', $item['produit_id'])
                    ->where('tenant_id', $tenantId)
                    ->first();

                if (!$produit || $produit->is_service) {
                    continue;
                }

                // Métadonnées du produit
                $updateMeta = [];
                if (isset($item['seuil_alerte']) && $item['seuil_alerte'] !== null) {
                    $updateMeta['seuil_alerte'] = $item['seuil_alerte'];
                }
                if (isset($item['emplacement_stock']) && $item['emplacement_stock'] !== null) {
                    $updateMeta['emplacement_stock'] = $item['emplacement_stock'];
                }
                if (!empty($updateMeta)) {
                    $produit->update($updateMeta);
                }

                // Ligne de stock
                $stockLine = Stock::firstOrCreate(
                    ['produit_id' => $produit->id, 'entrepot_id' => $entrepotId, 'tenant_id' => $tenantId],
                    ['quantite' => 0]
                );

                $currentQty = (float) $stockLine->quantite;
                $targetQty = (float) $item['quantite'];
                $delta = $targetQty - $currentQty;

                if ($delta != 0) {
                    $typeMouvement = $delta > 0 ? 'ajustement_positif' : 'ajustement_negatif';
                    $qtyToRecord = abs($delta);

                    $this->stockService->enregistrerMouvement(
                        $produit->id,
                        $qtyToRecord,
                        $typeMouvement,
                        'INITIALISATION_COMPLETE',
                        null,
                        $userId,
                        $tenantId,
                        $entrepotId
                    );
                }

                $updatedLinesCount++;
                $productsSet[$produit->id] = true;
            }
        });

        $distinctProductsCount = count($productsSet);

        return response()->json([
            'message' => "Initialisation complète du stock réalisée avec succès pour {$updatedLinesCount} ligne(s) dans {$distinctProductsCount} produit(s).",
            'lines_count' => $updatedLinesCount,
            'products_count' => $distinctProductsCount
        ]);
    }
}
