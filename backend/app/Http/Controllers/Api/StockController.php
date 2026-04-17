<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\MouvementStock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Stock::with(['produit:id,reference,designation,gerer_stock', 'entrepot'])
            ->when($request->entrepot_id, fn($q, $v) => $q->where('entrepot_id', $v))
            ->when($request->search, fn($q, $v) => $q->whereHas('produit', function($sq) use ($v) {
                $sq->where('designation', 'like', "%{$v}%")
                   ->orWhere('reference', 'like', "%{$v}%");
            }))
            ->orderBy($request->sort_by ?? 'quantite_physique', $request->sort_dir ?? 'asc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function show(Stock $stock): JsonResponse
    {
        $mouvements = MouvementStock::where('produit_id', $stock->produit_id)
            ->where('entrepot_id', $stock->entrepot_id)
            ->latest('date_mouvement')
            ->limit(20)
            ->get();

        return response()->json([
            'stock' => $stock->load(['produit', 'entrepot']),
            'mouvements' => $mouvements
        ]);
    }
}
