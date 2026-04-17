<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Projet::with(['client', 'etat'])
            ->when($request->client_id, fn($q, $v) => $q->where('client_id', $v))
            ->when($request->etat_id, fn($q, $v) => $q->where('etat_id', $v))
            ->when($request->search, fn($q, $v) => $q->where('nom', 'like', "%{$v}%")
               ->orWhere('reference', 'like', "%{$v}%"))
            ->orderBy('created_at', 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reference'      => 'required|string|max:50',
            'nom'            => 'required|string|max:255',
            'client_id'      => 'nullable|exists:clients,id',
            'description'    => 'nullable|string',
            'date_debut'     => 'nullable|date',
            'date_fin_prevue'=> 'nullable|date',
            'budget_estime'  => 'nullable|numeric',
            'etat_id'        => 'nullable|exists:etat_document,id',
        ]);

        $projet = Projet::create($data);
        return response()->json($projet->load('client', 'etat'), 201);
    }

    public function show(Projet $projet): JsonResponse
    {
        return response()->json($projet->load(['client', 'etat', 'devis', 'factures']));
    }
}
