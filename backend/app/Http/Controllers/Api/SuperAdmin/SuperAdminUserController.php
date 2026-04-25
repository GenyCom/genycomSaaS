<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class SuperAdminUserController extends Controller
{
    /**
     * GET /api/superadmin/users — Liste tous les utilisateurs de la plateforme (Base Centrale).
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with('tenants')
            ->when($request->search, function ($q, $v) {
                $q->where(function ($sq) use ($v) {
                    $sq->where('nom', 'like', "%{$v}%")
                        ->orWhere('prenom', 'like', "%{$v}%")
                        ->orWhere('email', 'like', "%{$v}%");
                });
            })
            ->when($request->is_superadmin !== null, fn($q) => $q->where('is_superadmin', $request->boolean('is_superadmin')))
            ->when($request->is_active !== null, fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('created_at', 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    /**
     * GET /api/superadmin/users/{user} — Détail d'un utilisateur et ses accès SaaS.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user->load('tenants'));
    }

    /**
     * POST /api/superadmin/users — Créer un nouvel utilisateur central.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom'           => 'required|string|max:255',
            'prenom'        => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'telephone'     => 'nullable|string|max:30',
            'is_active'     => 'sometimes|boolean',
            'is_superadmin' => 'sometimes|boolean',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user'    => $user->load('tenants'),
        ], 201);
    }

    /**
     * PUT/PATCH /api/superadmin/users/{user} — Modifier un utilisateur central.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'nom'           => 'sometimes|string|max:255',
            'prenom'        => 'sometimes|string|max:255',
            'email'         => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password'      => 'nullable|string|min:8|confirmed',
            'telephone'     => 'nullable|string|max:30',
            'is_active'     => 'sometimes|boolean',
            'is_superadmin' => 'sometimes|boolean',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'user'    => $user->fresh('tenants'),
        ]);
    }

    /**
     * DELETE /api/superadmin/users/{user} — Supprimer un utilisateur.
     */
    public function destroy(User $user): JsonResponse
    {
        // Sécurité : Ne pas se supprimer soi-même
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé de la base centrale.']);
    }
}