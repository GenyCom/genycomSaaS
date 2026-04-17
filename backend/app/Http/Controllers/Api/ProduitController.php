<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProduitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Produit::with(['famille', 'unite'])
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('reference', 'like', "%{$v}%")
                   ->orWhere('designation', 'like', "%{$v}%")
                   ->orWhere('code_barre', 'like', "%{$v}%");
            }))
            ->when($request->famille_id, fn($q, $v) => $q->where('famille_id', $v))
            ->orderBy($request->sort_by ?? 'designation', $request->sort_dir ?? 'asc');

        $produits = $request->per_page 
            ? $query->paginate($request->per_page) 
            : $query->get();

        return response()->json($produits);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reference'         => 'required|string|max:100',
            'designation'       => 'required|string|max:255',
            'type_produit'      => 'required|in:bien,service,matiere_premiere,produit_fini',
            'famille_id'        => 'nullable|exists:famille_produit,id',
            'unite_mesure_id'   => 'nullable|exists:unite_mesure,id',
            'prix_achat_ht'     => 'nullable|numeric|min:0',
            'prix_vente_ht'     => 'required|numeric|min:0',
            'taux_tva_id'       => 'nullable|exists:taux_tva,id',
            'code_barre'        => 'nullable|string|max:100',
            'description'       => 'nullable|string',
            'gerer_stock'       => 'boolean',
            'stock_min'         => 'nullable|numeric|min:0',
            'is_active'         => 'boolean',
        ]);

        $produit = Produit::create($data);
        return response()->json($produit->load(['famille', 'unite']), 201);
    }

    public function show(Produit $produit): JsonResponse
    {
        return response()->json($produit->load(['famille', 'unite', 'tauxTva', 'tarifs']));
    }

    public function update(Request $request, Produit $produit): JsonResponse
    {
        $data = $request->validate([
            'reference'         => 'sometimes|string|max:100',
            'designation'       => 'sometimes|string|max:255',
            'type_produit'      => 'nullable|in:bien,service,matiere_premiere,produit_fini',
            'famille_id'        => 'nullable|exists:famille_produit,id',
            'unite_mesure_id'   => 'nullable|exists:unite_mesure,id',
            'prix_achat_ht'     => 'nullable|numeric|min:0',
            'prix_vente_ht'     => 'nullable|numeric|min:0',
            'taux_tva_id'       => 'nullable|exists:taux_tva,id',
            'code_barre'        => 'nullable|string|max:100',
            'description'       => 'nullable|string',
            'gerer_stock'       => 'boolean',
            'stock_min'         => 'nullable|numeric|min:0',
            'is_active'         => 'boolean',
        ]);

        $produit->update($data);
        return response()->json($produit->fresh(['famille', 'unite']));
    }

    public function destroy(Produit $produit): JsonResponse
    {
        $produit->delete();
        return response()->json(['message' => 'Produit supprimé']);
    }
}
