<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Depense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Depense::with(['categorie', 'fournisseur', 'reglement.modeReglement'])
            ->when($request->categorie_id, fn($q, $v) => $q->where('categorie_id', $v))
            ->when($request->date_debut, fn($q, $v) => $q->where('date_depense', '>=', $v))
            ->when($request->date_fin, fn($q, $v) => $q->where('date_depense', '<=', $v))
            ->orderBy('date_depense', 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'categorie_id'   => 'required|exists:categorie_depense,id',
            'fournisseur_id' => 'nullable|exists:fournisseurs,id',
            'date_depense'   => 'required|date',
            'montant_ht'     => 'required|numeric|min:0',
            'montant_ttc'    => 'required|numeric|min:0',
            'description'    => 'required|string',
            'reference_facture' => 'nullable|string',
            'est_payee'      => 'boolean',
        ]);

        $depense = Depense::create($data);
        return response()->json($depense->load('categorie'), 201);
    }
    
    public function destroy(Depense $depense): JsonResponse
    {
        $depense->delete();
        return response()->json(['message' => 'Dépense supprimée']);
    }
}
