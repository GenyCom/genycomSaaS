<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\MouvementStock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

        $this->stockService->ajusterManual(
            $data['produit_id'],
            $data['entrepot_id'] ?? null,
            $data['quantite'],
            $data['type'],
            auth()->id(),
            1 // tenant_id
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
            // Logique de transfert (directement via service pour transaction atomique)
            DB::transaction(function() use ($data) {
                $this->stockService->enregistrerMouvement(
                    $data['produit_id'], $data['quantite'], 'transfert_out', 'TRANSFER', null, auth()->id(), 1, $data['entrepot_source_id']
                );
                $this->stockService->enregistrerMouvement(
                    $data['produit_id'], $data['quantite'], 'transfert_in', 'TRANSFER', null, auth()->id(), 1, $data['entrepot_dest_id']
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
}
