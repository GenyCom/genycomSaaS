<?php

namespace App\Http\Controllers\Api\Parametrage;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TenantUserController extends Controller
{
    /**
     * Liste tous les sous-comptes rattachés au tenant actuel.
     */
    public function index(Request $request)
    {
        $tenant = $request->get('current_tenant');
        if (!$tenant) {
            return response()->json(['message' => 'Tenant introuvable'], 404);
        }

        $users = DB::connection('central')
            ->table('tenant_user')
            ->join('users', 'users.id', '=', 'tenant_user.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'tenant_user.role_id')
            ->where('tenant_user.tenant_id', $tenant->id)
            ->whereNull('users.deleted_at')
            ->select(
                'users.id',
                'users.nom',
                'users.prenom',
                'users.email',
                'users.telephone',
                'users.is_active',
                'tenant_user.is_owner',
                'tenant_user.role_id',
                'roles.name as role_name',
                'roles.description as role_description',
                'tenant_user.created_at'
            )
            ->get();

        return response()->json($users);
    }

    /**
     * Créer un nouveau sous-compte ou y rattacher un compte existant.
     */
    public function store(Request $request)
    {
        $tenant = $request->get('current_tenant');
        if (!$tenant) {
            return response()->json(['message' => 'Tenant introuvable'], 404);
        }

        $validated = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email',
            'password'  => 'required|string|min:6',
            'telephone' => 'nullable|string|max:30',
            'role_id'   => 'required|integer|exists:central.roles,id',
        ]);

        $email = strtolower(trim($validated['email']));

        // Vérifier si l'utilisateur existe déjà en central
        $user = User::where('email', $email)->first();

        if ($user) {
            // Vérifier s'il est déjà rattaché à ce tenant
            $alreadyAttached = DB::connection('central')->table('tenant_user')
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyAttached) {
                throw ValidationException::withMessages([
                    'email' => ['Cet utilisateur est déjà membre de votre entreprise.'],
                ]);
            }
        } else {
            // Créer le nouvel utilisateur central
            $user = User::create([
                'nom'       => $validated['nom'],
                'prenom'    => $validated['prenom'],
                'email'     => $email,
                'password'  => Hash::make($validated['password']),
                'telephone' => $validated['telephone'] ?? null,
                'is_active' => true,
            ]);
        }

        // Rattacher le user au tenant
        DB::connection('central')->table('tenant_user')->insert([
            'tenant_id'  => $tenant->id,
            'user_id'    => $user->id,
            'role_id'    => $validated['role_id'],
            'is_owner'   => false,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Sous-compte créé et rattaché avec succès',
            'user_id' => $user->id
        ], 201);
    }

    /**
     * Afficher les détails d'un sous-compte.
     */
    public function show(Request $request, $id)
    {
        $tenant = $request->get('current_tenant');

        $user = DB::connection('central')
            ->table('tenant_user')
            ->join('users', 'users.id', '=', 'tenant_user.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'tenant_user.role_id')
            ->where('tenant_user.tenant_id', $tenant->id)
            ->where('tenant_user.user_id', $id)
            ->select(
                'users.id',
                'users.nom',
                'users.prenom',
                'users.email',
                'users.telephone',
                'users.is_active',
                'tenant_user.is_owner',
                'tenant_user.role_id',
                'roles.name as role_name'
            )
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        return response()->json($user);
    }

    /**
     * Mettre à jour un sous-compte.
     */
    public function update(Request $request, $id)
    {
        $tenant = $request->get('current_tenant');

        $pivot = DB::connection('central')->table('tenant_user')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $id)
            ->first();

        if (!$pivot) {
            return response()->json(['message' => 'Utilisateur non trouvé dans cette entreprise.'], 404);
        }

        $validated = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'role_id'   => 'required|integer|exists:central.roles,id',
            'is_active' => 'boolean',
        ]);

        // Empêcher la désactivation ou le changement de rôle de l'Owner principal
        if ($pivot->is_owner) {
            $validated['is_active'] = true;
        }

        // Mettre à jour le profil User
        $user = User::findOrFail($id);
        $user->update([
            'nom'       => $validated['nom'],
            'prenom'    => $validated['prenom'],
            'telephone' => $validated['telephone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Mettre à jour la table pivot
        DB::connection('central')->table('tenant_user')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $id)
            ->update([
                'role_id' => $validated['role_id'],
            ]);

        return response()->json(['message' => 'Sous-compte mis à jour avec succès.']);
    }

    /**
     * Réinitialiser le mot de passe d'un sous-compte.
     */
    public function updatePassword(Request $request, $id)
    {
        $tenant = $request->get('current_tenant');

        $exists = DB::connection('central')->table('tenant_user')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $id)
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }

    /**
     * Supprimer/détacher un sous-compte de l'entreprise.
     */
    public function destroy(Request $request, $id)
    {
        $tenant = $request->get('current_tenant');

        $pivot = DB::connection('central')->table('tenant_user')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $id)
            ->first();

        if (!$pivot) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        if ($pivot->is_owner) {
            return response()->json([
                'message' => 'Impossible de supprimer ou détacher le gérant principal de l\'entreprise.'
            ], 422);
        }

        DB::connection('central')->table('tenant_user')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $id)
            ->delete();

        return response()->json(['message' => 'Sous-compte supprimé de l\'entreprise avec succès.']);
    }
}
