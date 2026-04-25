<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SuperAdminDashboardController extends Controller
{
    /**
     * GET /api/superadmin/dashboard — KPIs globaux pour le SuperAdmin.
     */
    public function __invoke(): JsonResponse
    {
        // KPI 1 : Nombre total d'utilisateurs
        $totalUsers = User::count();

        // KPI 2 : Nombre de SuperAdmins
        $totalSuperadmins = User::where('is_superadmin', true)->count();

        // KPI 3 : Nombre de tenants (instances SaaS)
        $totalTenants = Tenant::count();

        // KPI 4 : Tenants actifs / suspendus / demo
        $tenantsByStatut = Tenant::select('statut', DB::raw('COUNT(*) as count'))
            ->groupBy('statut')
            ->pluck('count', 'statut')
            ->toArray();

        // KPI 5 : Utilisateurs actifs vs inactifs
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();

        // KPI 6 : Derniers utilisateurs inscrits (5 derniers)
        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'nom', 'prenom', 'email', 'is_superadmin', 'is_active', 'created_at']);

        // KPI 7 : Derniers tenants créés (5 derniers)
        $recentTenants = Tenant::withCount('users')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'nom', 'database_name', 'statut', 'created_at']);

        // KPI 8 : Utilisateurs connectés récemment (dernières 24h)
        $recentLogins = User::whereNotNull('last_login_at')
            ->where('last_login_at', '>=', now()->subHours(24))
            ->count();

        return response()->json([
            'kpis' => [
                'total_users'       => $totalUsers,
                'total_superadmins' => $totalSuperadmins,
                'active_users'      => $activeUsers,
                'inactive_users'    => $inactiveUsers,
                'total_tenants'     => $totalTenants,
                'tenants_actifs'    => $tenantsByStatut['actif'] ?? 0,
                'tenants_suspendus' => $tenantsByStatut['suspendu'] ?? 0,
                'tenants_demo'      => $tenantsByStatut['demo'] ?? 0,
                'recent_logins_24h' => $recentLogins,
            ],
            'recent_users'   => $recentUsers,
            'recent_tenants' => $recentTenants,
        ]);
    }
}
