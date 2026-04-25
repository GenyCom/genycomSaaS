<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    /**
     * Liste paginée des clients avec eager loading et recherche.
     */
    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['societe', 'code_client', 'email', 'ville', 'nom', 'created_at'];
        $sortBy = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : 'societe';
        $sortDir = $request->sort_dir === 'desc' ? 'desc' : 'asc';

        $query = Client::with(['typeClient', 'commercial:id,nom,prenom'])
            ->search($request->search)
            ->when($request->type_client_id, fn($q, $v) => $q->where('type_client_id', $v))
            ->orderBy($sortBy, $sortDir);

        $clients = $request->per_page 
            ? $query->paginate($request->per_page) 
            : $query->get();

        return response()->json($clients);
    }

    /**
     * Création sécurisée d'un client.
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        $client = Client::create($request->validated());
        return response()->json($client->load('typeClient'), 201);
    }

    /**
     * Récupération d'un client avec ses relations.
     */
    public function show(int $id): JsonResponse
    {
        $client = Client::with(['typeClient', 'commercial:id,nom,prenom', 'contacts'])->findOrFail($id);
        return response()->json($client);
    }

    /**
     * Mise à jour sécurisée d'un client.
     */
    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        $client = Client::findOrFail($id);
        $this->authorize('update', $client);

        $client->update($request->validated());
        return response()->json($client->fresh('typeClient'));
    }

    /**
     * Suppression avec gestion des contraintes d'intégrité.
     */
    public function destroy(int $id): JsonResponse
    {
        $client = Client::findOrFail($id);
        $this->authorize('delete', $client);

        try {
            $client->delete();
            return response()->json(['message' => 'Client supprimé']);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Impossible de supprimer ce client : des enregistrements liés existent (factures, devis, projets...).'
                ], 409);
            }
            throw $e;
        }
    }
}
