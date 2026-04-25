<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFournisseurRequest;
use App\Http\Requests\UpdateFournisseurRequest;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FournisseurController extends Controller
{
    /**
     * Liste paginée des fournisseurs avec eager loading et recherche.
     */
    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['societe', 'code_fournisseur', 'email', 'ville', 'nom', 'created_at'];
        $sortBy = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : 'societe';
        $sortDir = $request->sort_dir === 'desc' ? 'desc' : 'asc';

        $query = Fournisseur::with(['typeFournisseur'])
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('code_fournisseur', 'like', "%{$v}%")
                   ->orWhere('societe', 'like', "%{$v}%")
                   ->orWhere('email', 'like', "%{$v}%");
            }))
            ->orderBy($sortBy, $sortDir);

        $fournisseurs = $request->per_page 
            ? $query->paginate($request->per_page) 
            : $query->get();

        return response()->json($fournisseurs);
    }

    /**
     * Création sécurisée d'un fournisseur.
     */
    public function store(StoreFournisseurRequest $request): JsonResponse
    {
        $this->authorize('create', Fournisseur::class);

        $fournisseur = Fournisseur::create($request->validated());
        return response()->json($fournisseur->load('typeFournisseur'), 201);
    }

    /**
     * Récupération d'un fournisseur avec ses relations.
     */
    public function show(int $id): JsonResponse
    {
        $fournisseur = Fournisseur::with(['typeFournisseur', 'contacts'])->findOrFail($id);
        return response()->json($fournisseur);
    }

    /**
     * Mise à jour sécurisée d'un fournisseur.
     */
    public function update(UpdateFournisseurRequest $request, int $id): JsonResponse
    {
        $fournisseur = Fournisseur::findOrFail($id);
        $this->authorize('update', $fournisseur);

        $fournisseur->update($request->validated());
        return response()->json($fournisseur->fresh('typeFournisseur'));
    }

    /**
     * Suppression avec gestion des contraintes d'intégrité.
     */
    public function destroy(int $id): JsonResponse
    {
        $fournisseur = Fournisseur::findOrFail($id);
        $this->authorize('delete', $fournisseur);

        try {
            $fournisseur->delete();
            return response()->json(['message' => 'Fournisseur supprimé']);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Impossible de supprimer ce fournisseur : des enregistrements liés existent (commandes, produits...).'
                ], 409);
            }
            throw $e;
        }
    }
}
