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
        $tenant->load('users');
        return response()->json($tenant);
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
            'email'           => 'required|email',
            'adresse'         => 'required|string|max:500',
            'ville'           => 'required|string|max:100',
            'telephone'       => 'nullable|string|max:20',
            'forme_juridique' => 'nullable|string|max:100',
            'site_web'        => 'nullable|string|max:255',
            'logo_url'        => 'nullable|string|max:500',
        ]);

        // Mot de passe par défaut si l'utilisateur n'existe pas encore
        $data['nom']      = $data['nom_entreprise'];
        $data['prenom']   = 'Admin';
        $data['password'] = 'password123';
        $data['pays']     = 'Maroc';

        try {
            $result = $provisioning->provisionner($data);

            return response()->json([
                'message' => "Base de données [{$data['database_name']}] créée avec succès ! Les tables ont été générées.",
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
