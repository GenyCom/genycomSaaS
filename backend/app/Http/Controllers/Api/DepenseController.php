<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Depense;
use App\Models\Devise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepenseController extends Controller
{
    /**
     * Liste des dépenses (avec filtres et pagination)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Depense::with(['categorie'])
            ->when($request->categorie_id, fn($q, $v) => $q->where('categorie_id', $v))
            ->when($request->date_debut, fn($q, $v) => $q->where('date_depense', '>=', $v))
            ->when($request->date_fin, fn($q, $v) => $q->where('date_depense', '<=', $v))
            ->orderBy('date_depense', 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    /**
     * Afficher une dépense spécifique (Utilisé par DepenseDetail.vue)
     */
    public function show(Depense $depense): JsonResponse
    {
        return response()->json($depense->load('categorie'));
    }

    /**
     * Créer une nouvelle dépense
     */
    public function store(Request $request): JsonResponse
    {
        // Validation stricte basée sur les colonnes de MariaDB
        $data = $request->validate([
            'categorie_id'   => 'nullable|integer',
            'date_depense'   => 'required|date',
            'montant'        => 'required|numeric|min:0',
            'libelle'        => 'required|string|max:255',
            'code'           => 'nullable|string|max:50',
            'etat_id'        => 'nullable|integer',
            'note_reglement' => 'nullable|string',
            'is_recurrente'  => 'boolean',
            'frequence'      => 'nullable|in:mensuel,trimestriel,annuel',
            'devise_id'      => 'nullable|integer',
            'taux_change_document' => 'nullable|numeric',
        ]);

        // Assure-toi que current_tenant est bien géré par ton application
        $tenantId = $request->get('current_tenant')->id ?? 1; 
        $data['tenant_id'] = $tenantId;
        $data['created_by'] = auth()->id();

        // Gestion automatique de la Devise et du Taux de change
        if (empty($data['devise_id'])) {
            $devise = Devise::where('tenant_id', $tenantId)->where('is_principale', true)->first();
            $data['devise_id'] = $devise?->id;
            $data['taux_change_document'] = 1.0;
        } elseif (empty($data['taux_change_document'])) {
            $devise = Devise::find($data['devise_id']);
            $data['taux_change_document'] = $devise?->taux_change ?? 1.0;
        }

        $depense = Depense::create($data);
        
        return response()->json($depense->load('categorie'), 201);
    }
    
    /**
     * Mettre à jour une dépense existante
     */
    public function update(Request $request, Depense $depense): JsonResponse
    {
        $data = $request->validate([
            'categorie_id'   => 'nullable|integer',
            'date_depense'   => 'required|date',
            'montant'        => 'required|numeric|min:0',
            'libelle'        => 'required|string|max:255',
            'code'           => 'nullable|string|max:50',
            'etat_id'        => 'nullable|integer',
            'note_reglement' => 'nullable|string',
            'is_recurrente'  => 'boolean',
            'frequence'      => 'nullable|in:mensuel,trimestriel,annuel',
        ]);

        // Nettoyage de la fréquence si la dépense n'est plus récurrente
        if (empty($data['is_recurrente']) || $data['is_recurrente'] === false) {
            $data['frequence'] = null;
        }

        $depense->update($data);
        
        return response()->json($depense->load('categorie'));
    }

    /**
     * Supprimer une dépense
     */
    public function destroy(Depense $depense): JsonResponse
    {
        $depense->delete();
        
        return response()->json(['message' => 'Dépense supprimée avec succès.']);
    }
}