<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FournisseurController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Fournisseur::with(['typeFournisseur'])
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('code_fournisseur', 'like', "%{$v}%")
                   ->orWhere('societe', 'like', "%{$v}%")
                   ->orWhere('email', 'like', "%{$v}%");
            }))
            ->orderBy($request->sort_by ?? 'societe', $request->sort_dir ?? 'asc');

        $fournisseurs = $request->per_page 
            ? $query->paginate($request->per_page) 
            : $query->get();

        return response()->json($fournisseurs);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'societe'               => 'required|string|max:255',
            'code_fournisseur'      => 'nullable|string|max:50',
            'is_personne_physique'  => 'boolean',
            'nom'                   => 'nullable|string|max:100',
            'prenom'                => 'nullable|string|max:100',
            'ice'                   => 'nullable|string|max:100',
            'adresse'               => 'nullable|string|max:500',
            'ville'                 => 'nullable|string|max:100',
            'code_postal'           => 'nullable|string|max:20',
            'pays'                  => 'nullable|string|max:100',
            'telephone'             => 'nullable|string|max:20',
            'mobile'                => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:255',
            'observations'          => 'nullable|string',
            'type_fournisseur_id'   => 'nullable|exists:type_fournisseur,id',
            'delai_livraison_jour'  => 'nullable|integer|min:0',
        ]);

        $fournisseur = Fournisseur::create($data);
        return response()->json($fournisseur->load('typeFournisseur'), 201);
    }

    public function show(Fournisseur $fournisseur): JsonResponse
    {
        return response()->json($fournisseur->load(['typeFournisseur', 'contacts']));
    }

    public function update(Request $request, Fournisseur $fournisseur): JsonResponse
    {
        $data = $request->validate([
            'societe'               => 'sometimes|string|max:255',
            'code_fournisseur'      => 'nullable|string|max:50',
            'is_personne_physique'  => 'boolean',
            'nom'                   => 'nullable|string|max:100',
            'prenom'                => 'nullable|string|max:100',
            'ice'                   => 'nullable|string|max:100',
            'adresse'               => 'nullable|string|max:500',
            'ville'                 => 'nullable|string|max:100',
            'code_postal'           => 'nullable|string|max:20',
            'pays'                  => 'nullable|string|max:100',
            'telephone'             => 'nullable|string|max:20',
            'mobile'                => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:255',
            'observations'          => 'nullable|string',
            'type_fournisseur_id'   => 'nullable|exists:type_fournisseur,id',
            'delai_livraison_jour'  => 'nullable|integer|min:0',
        ]);

        $fournisseur->update($data);
        return response()->json($fournisseur->fresh('typeFournisseur'));
    }

    public function destroy(Fournisseur $fournisseur): JsonResponse
    {
        $fournisseur->delete();
        return response()->json(['message' => 'Fournisseur supprimé']);
    }
}
