<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\TelemetryErrorLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SuperAdminDashboardController extends Controller
{
    /**
     * GET /api/superadmin/dashboard — KPIs globaux pour le SuperAdmin.
     */
    public function __invoke(Request $request): JsonResponse
    {
        return $this->stats($request);
    }

    /**
     * GET /api/superadmin/dashboard-stats — Métriques en temps réel, activité utilisateurs et télémétrie.
     */
    public function stats(Request $request): JsonResponse
    {
        $threshold15m = now()->subMinutes(15);
        $threshold24h = now()->subHours(24);

        // ─── 1. SUIVI DES TENANTS & SESSIONS EN TEMPS RÉEL ───────────────────
        $tenantsQuery = Tenant::withCount(['users as total_users_count'])
            ->withCount(['users as active_users_count' => function ($q) use ($threshold15m) {
                $q->where('last_seen_at', '>=', $threshold15m);
            }])
            ->get(['id', 'nom', 'plan', 'statut', 'created_at']);

        $activeTenants15mCount = $tenantsQuery->filter(fn($t) => $t->active_users_count > 0)->count();

        $tenantsByStatut = Tenant::select('statut', DB::raw('COUNT(*) as count'))
            ->groupBy('statut')
            ->pluck('count', 'statut')
            ->toArray();

        // ─── 2. DÉTAIL DES UTILISATEURS & ACTIVITÉ ───────────────────────────
        $totalUsers = User::count();
        $totalSuperadmins = User::where('is_superadmin', true)->count();
        $activeUsersCount = User::where('is_active', true)->count();

        $activeUsersList = User::with(['tenants:id,nom'])
            ->where('last_seen_at', '>=', $threshold15m)
            ->orWhere('last_login_at', '>=', $threshold24h)
            ->orderByDesc('last_seen_at')
            ->orderByDesc('last_login_at')
            ->limit(50)
            ->get()
            ->map(function ($u) use ($threshold15m) {
                $isOnline = $u->last_seen_at && $u->last_seen_at->gte($threshold15m);

                return [
                    'id'            => $u->id,
                    'nom'           => $u->nom,
                    'prenom'        => $u->prenom,
                    'full_name'     => "{$u->prenom} {$u->nom}",
                    'email'         => $u->email,
                    'is_superadmin' => (bool)$u->is_superadmin,
                    'is_online'     => $isOnline,
                    'tenants'       => $u->tenants->map(fn($t) => [
                        'id'   => $t->id,
                        'nom'  => $t->nom,
                        'role' => $t->pivot->role_id ?? null,
                    ]),
                    'ip'            => $u->last_seen_ip ?: $u->last_login_ip,
                    'last_login_at' => $u->last_login_at ? $u->last_login_at->toIso8601String() : null,
                    'last_seen_at'  => $u->last_seen_at ? $u->last_seen_at->toIso8601String() : null,
                ];
            });

        // ─── 3. TÉLÉMÉTRIE & SURVEILLANCE DES ERREURS (HTTP 500) ───────────────
        $totalErrors24h = TelemetryErrorLog::where('created_at', '>=', $threshold24h)->count();

        $errorsByTenant = TelemetryErrorLog::leftJoin('tenants', 'telemetry_error_logs.tenant_id', '=', 'tenants.id')
            ->where('telemetry_error_logs.created_at', '>=', $threshold24h)
            ->select(
                'telemetry_error_logs.tenant_id',
                DB::raw("COALESCE(tenants.nom, 'Plateforme Centrale') as tenant_name"),
                DB::raw('COUNT(*) as error_count')
            )
            ->groupBy('telemetry_error_logs.tenant_id', 'tenants.nom')
            ->get();

        $recentErrorLogs = TelemetryErrorLog::with(['tenant:id,nom', 'user:id,nom,prenom,email'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                $traceShort = $log->trace;
                if ($traceShort && mb_strlen($traceShort) > 400) {
                    $traceShort = mb_substr($traceShort, 0, 400) . "\n... [TRONQUÉ]";
                }

                return [
                    'id'          => $log->id,
                    'message'     => $log->message,
                    'trace_short' => $traceShort,
                    'url'         => $log->url,
                    'method'      => $log->method,
                    'status_code' => $log->status_code,
                    'tenant_id'   => $log->tenant_id,
                    'tenant_name' => $log->tenant ? $log->tenant->nom : 'Plateforme Centrale',
                    'user_id'     => $log->user_id,
                    'user_name'   => $log->user ? "{$log->user->prenom} {$log->user->nom}" : 'Non authentifié',
                    'user_email'  => $log->user ? $log->user->email : null,
                    'ip'          => $log->ip,
                    'created_at'  => $log->created_at ? $log->created_at->toIso8601String() : null,
                ];
            });

        return response()->json([
            'realtime_metrics' => [
                'active_tenants_15m' => $activeTenants15mCount,
                'total_tenants'      => $tenantsQuery->count(),
                'active_users_15m'   => User::where('last_seen_at', '>=', $threshold15m)->count(),
                'total_users'        => $totalUsers,
                'total_superadmins'  => $totalSuperadmins,
                'active_users_total' => $activeUsersCount,
                'errors_24h_count'   => $totalErrors24h,
                'tenants_actifs'     => $tenantsByStatut['actif'] ?? 0,
                'tenants_suspendus'  => $tenantsByStatut['suspendu'] ?? 0,
                'tenants_demo'       => $tenantsByStatut['demo'] ?? 0,
            ],
            'tenants' => $tenantsQuery->map(fn($t) => [
                'id'                 => $t->id,
                'nom'                => $t->nom,
                'plan'               => $t->plan ?: 'Business',
                'statut'             => $t->statut,
                'total_users_count'  => $t->total_users_count,
                'active_users_count' => $t->active_users_count,
                'created_at'         => $t->created_at ? $t->created_at->toIso8601String() : null,
            ]),
            'active_users' => $activeUsersList,
            'telemetry_errors' => [
                'total_24h'   => $totalErrors24h,
                'by_tenant'   => $errorsByTenant,
                'recent_logs' => $recentErrorLogs,
            ],
        ]);
    }
}
