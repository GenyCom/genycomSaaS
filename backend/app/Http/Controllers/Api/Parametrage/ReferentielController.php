<?php
namespace App\Http\Controllers\Api\Parametrage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReferentielController extends Controller
{
    /**
     * Map HTTP types to Eloquent Models
     */
    private function getModelClass(string $type): ?string
    {
        $map = [
            'taux-tva' => \App\Models\TauxTva::class,
            'devises' => \App\Models\Devise::class,
            'entrepots' => \App\Models\Entrepot::class,
            'modes-reglement' => \App\Models\ModeReglement::class,
            'conditions-reglement' => \App\Models\ConditionReglement::class,
            'familles-produit' => \App\Models\FamilleProduit::class,
            'types-client' => \App\Models\TypeClient::class,
            'types-fournisseur' => \App\Models\TypeFournisseur::class,
            'etats' => \App\Models\EtatDocument::class,
        ];
        
        return $map[$type] ?? null;
    }

    /**
     * Règles de validation dynamiques par type de référentiel.
     */
    private function getRulesFor(string $type, bool $isUpdate = false): array
    {
        $requiredOrSometimes = $isUpdate ? 'sometimes' : 'required';

        return match ($type) {
            'taux-tva' => [
                'taux'    => "{$requiredOrSometimes}|numeric|min:0|max:100",
                'libelle' => 'nullable|string|max:100',
                'detail'  => 'nullable|string|max:255',
                'actif'   => 'boolean',
            ],
            'devises' => [
                'nom'            => "{$requiredOrSometimes}|string|max:100",
                'code_iso'       => 'nullable|string|max:3',
                'symbole'        => 'nullable|string|max:10',
                'taux_change'    => 'nullable|numeric|min:0',
                'is_principale'  => 'boolean',
                'actif'          => 'boolean',
            ],
            'entrepots' => [
                'code'           => "{$requiredOrSometimes}|string|max:50",
                'nom'            => "{$requiredOrSometimes}|string|max:255",
                'adresse'        => 'nullable|string|max:500',
                'responsable_id' => 'nullable|integer',
                'is_default'     => 'boolean',
            ],
            'familles-produit' => [
                'code'      => "{$requiredOrSometimes}|string|max:50",
                'libelle'   => "{$requiredOrSometimes}|string|max:150",
                'detail'    => 'nullable|string|max:255',
                'parent_id' => 'nullable|integer|exists:tenant.famille_produit,id',
            ],
            'types-client' => [
                'libelle'    => "{$requiredOrSometimes}|string|max:100",
                'detail'     => 'nullable|string|max:255',
                'exempt_tva' => 'boolean',
                'vip'        => 'boolean',
            ],
            'types-fournisseur' => [
                'libelle' => "{$requiredOrSometimes}|string|max:100",
                'detail'  => 'nullable|string|max:255',
                'vip'     => 'boolean',
            ],
            'etats' => [
                'type_document' => "{$requiredOrSometimes}|string|max:50",
                'code'          => "{$requiredOrSometimes}|string|max:10",
                'libelle'       => "{$requiredOrSometimes}|string|max:50",
                'ordre'         => 'nullable|integer',
                'couleur'       => 'nullable|string|max:20',
                'detail'        => 'nullable|string',
            ],
            default => [
                'libelle' => "{$requiredOrSometimes}|string|max:100",
                'detail'  => 'nullable|string|max:255',
            ],
        };
    }

    public function index(Request $request, $type): JsonResponse
    {
        $class = $this->getModelClass($type);
        if (!$class) {
            return response()->json(['message' => "Type non reconnu : $type"], 404);
        }
        
        $query = $class::query();
        
        if ($type === 'familles-produit') {
            $query->with('parent');
        }

        if ($type === 'etats' && $request->has('type_document')) {
            $query->where('type_document', $request->type_document)->orderBy('ordre', 'asc');
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request, $type): JsonResponse
    {
        $class = $this->getModelClass($type);
        if (!$class) {
            return response()->json(['message' => "Type non reconnu"], 404);
        }
        
        $data = $request->validate($this->getRulesFor($type));
        $item = $class::create($data);
        return response()->json($item, 201);
    }

    public function show(Request $request, $type, $id): JsonResponse
    {
        $class = $this->getModelClass($type);
        if (!$class) {
            return response()->json(['message' => "Type non reconnu"], 404);
        }
        
        $item = $class::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, $type, $id): JsonResponse
    {
        $class = $this->getModelClass($type);
        if (!$class) {
            return response()->json(['message' => "Type non reconnu"], 404);
        }
        
        $item = $class::findOrFail($id);
        $data = $request->validate($this->getRulesFor($type, true));
        $item->update($data);
        
        return response()->json($item);
    }

    public function destroy(Request $request, $type, $id): JsonResponse
    {
        $class = $this->getModelClass($type);
        if (!$class) {
            return response()->json(['message' => "Type non reconnu"], 404);
        }
        
        $item = $class::findOrFail($id);

        try {
            $item->delete();
            return response()->json(['message' => 'Supprimé avec succès']);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Impossible de supprimer : cet élément est utilisé par d\'autres enregistrements.'
                ], 409);
            }
            throw $e;
        }
    }
}
