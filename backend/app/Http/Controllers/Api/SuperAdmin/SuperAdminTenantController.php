<?php
namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;

class SuperAdminTenantController extends Controller
{
    /**
     * GET /api/superadmin/tenants — Liste tous les tenants.
     */
    public function index()
    {
        $tenants = Tenant::withCount('users')->get()->map(function ($t) {
            return [
                'id'            => $t->id,
                'nom'           => $t->nom,
                'database_name' => $t->database_name,
                'domain'        => $t->domain,
                'statut'        => $t->statut,
                'users_count'   => $t->users_count,
                'created_at'    => $t->created_at,
            ];
        });

        return response()->json($tenants);
    }

    /**
     * GET /api/superadmin/tenants/{id} — Détail d'un tenant.
     */
    public function show(Tenant $tenant)
    {
        // Récupérer les utilisateurs liés au tenant avec leurs rôles respectifs depuis la base centrale
        $users = \Illuminate\Support\Facades\DB::connection('central')->table('tenant_user')
            ->join('users', 'tenant_user.user_id', '=', 'users.id')
            ->join('roles', 'tenant_user.role_id', '=', 'roles.id')
            ->where('tenant_user.tenant_id', $tenant->id)
            ->select('users.id', 'users.nom', 'users.prenom', 'users.email', 'roles.name as role_name', 'tenant_user.is_owner')
            ->get();

        // Calculer dynamiquement la taille de la base de données du tenant
        $dbSize = 0.0;
        try {
            $result = \Illuminate\Support\Facades\DB::connection('central')->select("
                SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$tenant->database_name]);
            $dbSize = round((float)($result[0]->size_mb ?? 0), 2);
        } catch (\Exception $e) {
            $dbSize = 0.0;
        }

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'nom' => $tenant->nom,
                'database_name' => $tenant->database_name,
                'domain' => $tenant->domain,
                'statut' => $tenant->statut,
                'created_at' => $tenant->created_at,
            ],
            'users' => $users,
            'db_size_mb' => $dbSize,
        ]);
    }

    /**
     * PUT /api/superadmin/tenants/{id} — Modifier un tenant (statut, nom...).
     */
    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'nom'    => 'sometimes|required|string|max:255',
            'domain' => 'sometimes|nullable|string|max:255|unique:tenants,domain,' . $tenant->id,
            'statut' => 'sometimes|required|in:actif,suspendu,demo',
        ]);

        $tenant->update($data);

        return response()->json([
            'message' => 'Tenant mis à jour avec succès.',
            'tenant'  => $tenant
        ]);
    }

    /**
     * POST /api/superadmin/tenants — Provisionner un nouveau tenant SaaS.
     * Crée la base de données, exécute les scripts SQL 002→008, crée le tenant
     * et rattache l'utilisateur.
     */
    public function store(Request $request, TenantProvisioningService $provisioning)
    {
        $data = $request->validate([
            'nom_entreprise'  => 'required|string|max:255',
            'database_name'   => 'required|string|max:100|regex:/^[a-z0-9_]+$/|unique:tenants,database_name',
            'db_username'     => 'nullable|string|max:100',
            'db_password'     => 'nullable|string|max:100',
            'email'           => 'required|email',
            'adresse'         => 'required|string|max:500',
            'ville'           => 'required|string|max:100',
            'telephone'       => 'nullable|string|max:20',
            'forme_juridique' => 'nullable|string|max:100',
            'site_web'        => 'nullable|string|max:255',
            'logo_url'        => 'nullable|string|max:500',
            'confirm_existing'=> 'nullable|boolean',
        ]);

        // Mot de passe par défaut si l'utilisateur n'existe pas encore
        $data['nom']      = $data['nom_entreprise'];
        $data['prenom']   = 'Admin';
        $data['password'] = 'password123';
        $data['pays']     = 'Maroc';

        $force = (bool) ($request->confirm_existing ?? false);

        try {
            $result = $provisioning->provisionner($data, $force);

            return response()->json([
                'message' => $force ? "Tenant rattaché à la base existante avec succès !" : "Base de données [{$data['database_name']}] créée avec succès !",
                'tenant'  => [
                    'id'            => $result['tenant']->id,
                    'nom'           => $result['tenant']->nom,
                    'database_name' => $result['tenant']->database_name,
                    'domain'        => $result['tenant']->domain,
                    'statut'        => $result['tenant']->statut,
                    'created_at'    => $result['tenant']->created_at,
                ],
                'user' => [
                    'id'    => $result['user']->id,
                    'email' => $result['user']->email,
                    'nom'   => $result['user']->full_name,
                ],
            ], 201);
        } catch (\Exception $e) {
            if ($e->getMessage() === "DATABASE_EXISTS") {
                return response()->json([
                    'code'    => 'DATABASE_EXISTS',
                    'message' => "La base de données [{$data['database_name']}] existe déjà. Voulez-vous l'utiliser et exécuter les scripts d'initialisation ?",
                ], 409);
            }

            return response()->json([
                'message' => "Erreur lors du provisioning : " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/superadmin/tenants/{id} — Supprimer un tenant.
     */
    public function destroy(Tenant $tenant)
    {
        // Optionnellement, supprimer la base de données
        // DB::statement("DROP DATABASE IF EXISTS `{$tenant->database_name}`");
        $tenant->delete();

        return response()->json(['message' => 'Tenant supprimé.']);
    }
}
