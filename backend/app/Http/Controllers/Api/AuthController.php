<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/register — Inscription (création d'un tenant SaaS).
     */
    public function register(Request $request, TenantProvisioningService $provisioning)
    {
        $data = $request->validate([
            'nom'               => 'required|string|max:100',
            'prenom'            => 'required|string|max:100',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|string|min:8|confirmed',
            'nom_entreprise'    => 'required|string|max:255',
            'adresse'           => 'nullable|string',
            'ville'             => 'nullable|string',
            'pays'              => 'nullable|string',
            'plan'              => 'nullable|in:free,starter,pro,enterprise',
        ]);

        $result = $provisioning->provisionner($data);
        $token = $result['user']->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Compte créé avec succès',
            'user'    => $this->formatUser($result['user']),
            'tenant'  => $result['tenant'],
            'token'   => $token,
        ], 201);
    }

    /**
     * POST /api/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Votre compte est désactivé.'],
            ]);
        }

        // Mettre à jour last_login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    /**
     * GET /api/me
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    private function formatUser(User $user): array
    {
        $user->load('roles.permissions', 'tenant.entreprise');
        return [
            'id'            => $user->id,
            'nom'           => $user->nom,
            'prenom'        => $user->prenom,
            'full_name'     => $user->full_name,
            'email'         => $user->email,
            'telephone'     => $user->telephone,
            'avatar_path'   => $user->avatar_path,
            'is_owner'      => $user->is_owner,
            'roles'         => $user->roles->pluck('name'),
            'permissions'   => $user->roles->flatMap->permissions->pluck('name')->unique()->values(),
            'tenant'        => [
                'id'    => $user->tenant->id,
                'nom'   => $user->tenant->nom,
                'slug'  => $user->tenant->slug,
                'plan'  => $user->tenant->plan,
            ],
            'entreprise'    => $user->tenant->entreprise,
        ];
    }
}
