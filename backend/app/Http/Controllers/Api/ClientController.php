<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Client::with(['typeClient', 'commercial:id,nom,prenom'])
            ->search($request->search)
            ->when($request->type_client_id, fn($q, $v) => $q->where('type_client_id', $v))
            ->orderBy($request->sort_by ?? 'societe', $request->sort_dir ?? 'asc');

        $clients = $request->per_page 
            ? $query->paginate($request->per_page) 
            : $query->get();

        return response()->json($clients);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'societe'               => 'required|string|max:255',
            'code_client'           => 'nullable|string|max:50',
            'is_personne_physique'  => 'boolean',
            'civilite'              => 'nullable|string|max:10',
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
            'exempt_tva'            => 'boolean',
            'type_client_id'        => 'nullable|exists:type_client,id',
            'plafond_credit'        => 'nullable|numeric|min:0',
            'delai_paiement'        => 'nullable|integer|min:0',
        ]);

        $client = Client::create($data);
        return response()->json($client->load('typeClient'), 201);
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json(
            $client->load(['typeClient', 'contacts', 'adressesLivraison', 'adressesFacturation', 'commercial:id,nom,prenom'])
        );
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $data = $request->validate([
            'societe'               => 'sometimes|string|max:255',
            'code_client'           => 'nullable|string|max:50',
            'is_personne_physique'  => 'boolean',
            'civilite'              => 'nullable|string|max:10',
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
            'exempt_tva'            => 'boolean',
            'type_client_id'        => 'nullable|exists:type_client,id',
            'plafond_credit'        => 'nullable|numeric|min:0',
            'delai_paiement'        => 'nullable|integer|min:0',
        ]);

        $client->update($data);
        return response()->json($client->fresh('typeClient'));
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();
        return response()->json(['message' => 'Client supprimé']);
    }
}
