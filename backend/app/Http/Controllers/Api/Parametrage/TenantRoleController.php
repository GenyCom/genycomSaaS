<?php

namespace App\Http\Controllers\Api\Parametrage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantRoleController extends Controller
{
    /**
     * Obtenir tous les rôles et leurs permissions associées.
     */
    public function index(Request $request)
    {
        $roles = DB::connection('central')->table('roles')->get();

        $result = [];

        foreach ($roles as $role) {
            $permissions = DB::connection('central')
                ->table('permission_role')
                ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('permission_role.role_id', $role->id)
                ->select('permissions.id', 'permissions.name', 'permissions.display_name', 'permissions.module')
                ->get();

            $result[] = [
                'id'           => $role->id,
                'name'         => $role->name,
                'description'  => $role->description,
                'is_system'    => (bool) $role->is_system,
                'permissions'  => $permissions,
                'permission_ids' => $permissions->pluck('id')->toArray(),
            ];
        }

        return response()->json($result);
    }

    /**
     * Dictionnaire de toutes les permissions disponibles groupées par module.
     */
    public function permissions(Request $request)
    {
        $permissions = DB::connection('central')
            ->table('permissions')
            ->select('id', 'name', 'display_name', 'description', 'module')
            ->get();

        $grouped = $permissions->groupBy('module');

        return response()->json([
            'all'     => $permissions,
            'grouped' => $grouped
        ]);
    }

    /**
     * Créer un rôle sur-mesure.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string|max:255',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'integer|exists:central.permissions,id',
        ]);

        $name = trim($validated['name']);

        $exists = DB::connection('central')->table('roles')->where('name', $name)->exists();
        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['Un rôle avec ce nom existe déjà.'],
            ]);
        }

        $roleId = DB::connection('central')->table('roles')->insertGetId([
            'name'        => $name,
            'description' => $validated['description'] ?? null,
            'is_system'   => false,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        if (!empty($validated['permissions'])) {
            $insertData = array_map(function ($permId) use ($roleId) {
                return [
                    'role_id'       => $roleId,
                    'permission_id' => $permId,
                ];
            }, $validated['permissions']);

            DB::connection('central')->table('permission_role')->insert($insertData);
        }

        return response()->json([
            'message' => 'Rôle créé avec succès.',
            'role_id' => $roleId
        ], 201);
    }

    /**
     * Mettre à jour un rôle.
     */
    public function update(Request $request, $id)
    {
        $role = DB::connection('central')->table('roles')->where('id', $id)->first();
        if (!$role) {
            return response()->json(['message' => 'Rôle introuvable.'], 404);
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string|max:255',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'integer|exists:central.permissions,id',
        ]);

        $name = trim($validated['name']);

        // Vérifier l'unicité du nom hors rôle courant
        $exists = DB::connection('central')->table('roles')
            ->where('name', $name)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['Un rôle avec ce nom existe déjà.'],
            ]);
        }

        // Si rôle système, on met à jour la description et permissions mais pas le nom
        $updateFields = [
            'description' => $validated['description'] ?? null,
            'updated_at'  => now(),
        ];

        if (!$role->is_system) {
            $updateFields['name'] = $name;
        }

        DB::connection('central')->table('roles')->where('id', $id)->update($updateFields);

        // Mettre à jour les permissions
        DB::connection('central')->table('permission_role')->where('role_id', $id)->delete();

        if (!empty($validated['permissions'])) {
            $insertData = array_map(function ($permId) use ($id) {
                return [
                    'role_id'       => $id,
                    'permission_id' => $permId,
                ];
            }, array_unique($validated['permissions']));

            DB::connection('central')->table('permission_role')->insert($insertData);
        }

        return response()->json(['message' => 'Rôle mis à jour avec succès.']);
    }

    /**
     * Supprimer un rôle sur-mesure.
     */
    public function destroy(Request $request, $id)
    {
        $role = DB::connection('central')->table('roles')->where('id', $id)->first();
        if (!$role) {
            return response()->json(['message' => 'Rôle introuvable.'], 404);
        }

        if ($role->is_system) {
            return response()->json(['message' => 'Les rôles système par défaut ne peuvent pas être supprimés.'], 422);
        }

        // Vérifier si le rôle est assigné à des utilisateurs
        $inUse = DB::connection('central')->table('tenant_user')->where('role_id', $id)->exists();
        if ($inUse) {
            return response()->json([
                'message' => 'Impossible de supprimer ce rôle car il est actuellement attribué à un ou plusieurs utilisateurs.'
            ], 422);
        }

        DB::connection('central')->table('permission_role')->where('role_id', $id)->delete();
        DB::connection('central')->table('roles')->where('id', $id)->delete();

        return response()->json(['message' => 'Rôle supprimé avec succès.']);
    }
}
