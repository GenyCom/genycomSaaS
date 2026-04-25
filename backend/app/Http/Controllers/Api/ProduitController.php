<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProduitRequest;
use App\Http\Requests\UpdateProduitRequest;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProduitController extends Controller
{
    /**
     * Liste paginée des produits avec eager loading et recherche.
     */
    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['designation', 'reference', 'prix_ht_vente', 'stock_actuel', 'created_at', 'marque'];
        $sortBy = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : 'designation';
        $sortDir = $request->sort_dir === 'desc' ? 'desc' : 'asc';

        $query = Produit::with(['famille', 'fournisseur:id,societe'])
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('reference', 'like', "%{$v}%")
                   ->orWhere('designation', 'like', "%{$v}%")
                   ->orWhere('code_barre', 'like', "%{$v}%");
            }))
            ->when($request->famille_id, fn($q, $v) => $q->where('famille_id', $v))
            ->orderBy($sortBy, $sortDir);

        $produits = $request->per_page 
            ? $query->paginate($request->per_page) 
            : $query->get();

        return response()->json($produits);
    }

    /**
     * Generate a unique reference from designation: [3 first chars UPPER]_[increment padded 4]
     */
    private function generateReference(string $designation): string
    {
        $prefix = mb_strtoupper(mb_substr(trim($designation), 0, 3));
        if (mb_strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }

        $lastRef = Produit::withoutGlobalScopes()
            ->where('reference', 'like', $prefix . '_%')
            ->orderByRaw("CAST(SUBSTRING_INDEX(reference, '_', -1) AS UNSIGNED) DESC")
            ->value('reference');

        $nextNum = 1;
        if ($lastRef) {
            $parts = explode('_', $lastRef);
            $lastNum = (int) end($parts);
            $nextNum = $lastNum + 1;
        }

        return $prefix . '_' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Création sécurisée d'un produit.
     */
    public function store(StoreProduitRequest $request): JsonResponse
    {
        $this->authorize('create', Produit::class);

        $data = $request->validated();

        if (empty($data['reference'])) {
            $data['reference'] = $this->generateReference($data['designation']);
        }

        if ($request->hasFile('image_upload')) {
            $path = $request->file('image_upload')->store('produits', 'public');
            $data['image_path'] = '/storage/' . $path;
        }

        $produit = Produit::create($data);
        return response()->json($produit->load(['famille', 'fournisseur:id,societe']), 201);
    }

    /**
     * Récupération d'un produit avec ses relations.
     */
    public function show(int $id): JsonResponse
    {
        $produit = Produit::with(['famille', 'fournisseur:id,societe'])->findOrFail($id);
        return response()->json($produit);
    }

    /**
     * Mise à jour sécurisée d'un produit.
     */
    public function update(UpdateProduitRequest $request, int $id): JsonResponse
    {
        $produit = Produit::findOrFail($id);
        $this->authorize('update', $produit);

        $data = $request->validated();
        if ($request->hasFile('image_upload')) {
            $path = $request->file('image_upload')->store('produits', 'public');
            $data['image_path'] = '/storage/' . $path;
        }

        $produit->update($data);
        return response()->json($produit->fresh(['famille', 'fournisseur:id,societe']));
    }

    /**
     * Suppression avec gestion des contraintes d'intégrité.
     */
    public function destroy(int $id): JsonResponse
    {
        $produit = Produit::findOrFail($id);
        $this->authorize('delete', $produit);

        try {
            $produit->delete();
            return response()->json(['message' => 'Produit supprimé']);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Impossible de supprimer ce produit : il est utilisé dans des factures, devis ou commandes.'
                ], 409);
            }
            throw $e;
        }
    }

    /**
     * Return the next available reference for a given designation.
     */
    public function nextReference(Request $request): JsonResponse
    {
        $designation = $request->query('designation', '');
        if (mb_strlen(trim($designation)) < 1) {
            return response()->json(['reference' => '']);
        }

        return response()->json([
            'reference' => $this->generateReference($designation)
        ]);
    }
}
