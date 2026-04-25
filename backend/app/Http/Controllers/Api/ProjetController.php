<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjetRequest;
use App\Http\Requests\UpdateProjetRequest;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjetController extends Controller
{
    /**
     * Liste paginée des projets avec eager loading et recherche.
     */
    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['nom_projet', 'code_projet', 'statut', 'date_debut', 'priorite', 'created_at'];
        $sortBy = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'desc' ? 'desc' : 'asc';

        $query = Projet::with(['client:id,societe,nom,prenom', 'responsable:id,nom,prenom', 'etat'])
            ->when($request->client_id, fn($q, $v) => $q->where('client_id', $v))
            ->when($request->etat_id, fn($q, $v) => $q->where('etat_id', $v))
            ->when($request->statut, fn($q, $v) => $q->where('statut', $v))
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('nom_projet', 'like', "%{$v}%")
                   ->orWhere('code_projet', 'like', "%{$v}%");
            }))
            ->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    /**
     * Création sécurisée d'un projet.
     */
    public function store(StoreProjetRequest $request): JsonResponse
    {
        $this->authorize('create', Projet::class);

        $data = $request->validated();
        
        // Attribution de l'état par défaut si non spécifié
        if (empty($data['etat_id'])) {
            $etatParDefaut = \App\Models\EtatDocument::where('type_document', 'projet')
                ->where('is_default', true)
                ->first();
            if ($etatParDefaut) {
                $data['etat_id'] = $etatParDefaut->id;
            }
        }

        // Gestion Devise & Taux (Snapshot)
        $tenantId = $request->get('current_tenant')->id;
        if (empty($data['devise_id'])) {
            $devise = \App\Models\Devise::where('tenant_id', $tenantId)->where('is_principale', true)->first();
            $data['devise_id'] = $devise?->id;
            $data['taux_change_document'] = 1.0;
        } else {
            if (empty($data['taux_change_document'])) {
                $devise = \App\Models\Devise::find($data['devise_id']);
                $data['taux_change_document'] = $devise?->taux_change ?? 1.0;
            }
        }

        $projet = Projet::create($data);
        return response()->json($projet->load(['client:id,societe,nom,prenom', 'etat']), 201);
    }

    /**
     * Récupération d'un projet avec ses relations.
     */
    public function show(int $id): JsonResponse
    {
        $projet = Projet::with(['client:id,societe,nom,prenom', 'responsable:id,nom,prenom', 'etat', 'devis', 'factures'])->findOrFail($id);
        return response()->json($projet);
    }

    /**
     * Mise à jour sécurisée d'un projet.
     */
    public function update(UpdateProjetRequest $request, int $id): JsonResponse
    {
        $projet = Projet::findOrFail($id);
        $this->authorize('update', $projet);

        $data = $request->validated();
        
        // Gestion Devise & Taux (Update)
        if (!empty($data['devise_id']) && empty($data['taux_change_document'])) {
            $devise = \App\Models\Devise::find($data['devise_id']);
            $data['taux_change_document'] = $devise?->taux_change ?? 1.0;
        }

        $projet->update($data);
        return response()->json($projet->fresh('client:id,societe,nom,prenom'));
    }

    /**
     * Suppression avec gestion des contraintes d'intégrité.
     */
    public function destroy(int $id): JsonResponse
    {
        $projet = Projet::findOrFail($id);
        $this->authorize('delete', $projet);

        try {
            $projet->delete();
            return response()->json(['message' => 'Projet supprimé']);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Impossible de supprimer ce projet : des documents liés existent (devis, factures...).'
                ], 409);
            }
            throw $e;
        }
    }
}
