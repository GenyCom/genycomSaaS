<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

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

        // Protection contre la force brute : 5 tentatives par minute par IP/Email
        $throttleKey = strtolower($request->email) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => ["Trop de tentatives. Veuillez réessayer dans {$seconds} secondes."],
            ]);
        }

        // On s'assure de chercher dans la connexion 'central' si nécessaire
        $user = User::where('email', strtolower(trim($request->email)))->first();

        // Vérification de l'existence et du mot de passe
        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        // Vérification si le compte est suspendu
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Votre compte est désactivé.'],
            ]);
        }

        // Réinitialiser le compteur de tentatives en cas de succès
        RateLimiter::clear($throttleKey);

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

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => $this->formatUser($user),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'L\'ancien mot de passe est incorrect.'], 400);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json(['message' => 'Mot de passe modifié avec succès.']);
    }

    private function formatUser(User $user): array
    {
        // Charger les tenants (avec pivot role_id, is_owner)
        $user->load(['tenants' => function($query) {
            $query->withPivot('role_id', 'is_owner');
        }]);
        
        $tenant = $user->tenant; // Accessor: premier tenant
        
        // Récupérer le rôle depuis la table pivot si tenant existe
        $roleName = null;
        $isOwner = false;

        if ($tenant && $tenant->pivot->role_id) {
            // Force l'usage de la connexion 'central' pour les rôles
            $role = DB::connection('central')->table('roles')->where('id', $tenant->pivot->role_id)->first();
            $roleName = $role?->name;
            $isOwner = (bool) $tenant->pivot->is_owner;
        }

        // Data related to the company
        $entrepriseInfo = null;

        if ($tenant) {
            try {
                // Configurer la connexion SQL au tenant temporairement
                config(['database.connections.tenant.database' => $tenant->database_name]);
                DB::purge('tenant');
                
                $entreprise = DB::connection('tenant')->table('entreprise')->first();
                if ($entreprise) {
                    $entrepriseInfo = [
                        'raison_sociale' => $entreprise->raison_sociale,
                        'forme_juridique' => $entreprise->forme_juridique,
                        'adresse' => $entreprise->adresse,
                        'ville' => $entreprise->ville,
                        'telephone' => $entreprise->telephone,
                        'email' => $entreprise->email,
                        'site_web' => $entreprise->site_web,
                        'logo' => $entreprise->logo_path,
                    ];
                }
            } catch (\Exception $e) {
                // Au cas où la base de données ne serait pas encore générée ou joignable
            }
        }

        return [
            'id'            => $user->id,
            'nom'           => $user->nom,
            'prenom'        => $user->prenom,
            'full_name'     => $user->full_name,
            'email'         => $user->email,
            'telephone'     => $user->telephone ?? null,
            'avatar_path'   => $user->avatar_path ?? null,
            'is_owner'      => $isOwner,
            'is_superadmin' => (bool) $user->is_superadmin,
            'roles'         => $roleName ? [$roleName] : ($user->is_superadmin ? ['superadmin'] : []),
            'permissions'   => [],
            'tenant'        => $tenant ? [
                'id'    => $tenant->id,
                'nom'   => $tenant->nom,
                'slug'  => $tenant->domain,
                'plan'  => $tenant->statut,
            ] : null,
            'entreprise'    => $entrepriseInfo,
            'app_version'   => env('APP_VERSION', '1.0'),
        ];
    }
}
