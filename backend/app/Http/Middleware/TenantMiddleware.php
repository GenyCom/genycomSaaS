<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Le SuperAdmin naviguant sur l'espace central n'a pas besoin de base tenant
        if ($user && $user->is_superadmin && $request->is('api/superadmin/*')) {
            return $next($request);
        }

        // Si l'utilisateur n'a pas spécifié de tenant dans la requête (header ou query),
        // on prend le premier tenant auquel il est rattaché.
        $tenantId = $request->header('X-Tenant-ID') ?: $request->tenant_id;
        
        $tenant = null;
        if ($tenantId) {
            // Un SuperAdmin peut accéder à n'importe quel tenant s'il spécifie l'ID
            if ($user && $user->is_superadmin) {
                $tenant = \App\Models\Tenant::find($tenantId);
            } else {
                $tenant = $user->tenants()->find($tenantId);
            }
        } else {
            $tenant = $user->tenants()->first();
            
            // Fallback pour SuperAdmin : on cherche en priorité le tenant de démonstration ID 2 (Sorec)
            if (!$tenant && $user && $user->is_superadmin) {
                $tenant = \App\Models\Tenant::find(2) ?: \App\Models\Tenant::whereHas('users')->first() ?: \App\Models\Tenant::first();
            }
        }

        if (!$tenant) {
            return response()->json(['message' => 'Aucun accès à une entreprise SaaS'], 403);
        }

        // Configuration dynamique de la base de données du locataire
        Config::set('database.connections.tenant.database', $tenant->database_name);
        
        if ($tenant->db_username) {
            Config::set('database.connections.tenant.username', $tenant->db_username);
        }
        if ($tenant->db_password) {
            Config::set('database.connections.tenant.password', $tenant->db_password);
        }

        DB::purge('tenant'); // Force la reconnexion avec les paramètres modifiés
        
        // Optionnel : Injecter le tenant dans la requête pour y accéder plus tard
        $request->merge(['current_tenant' => $tenant]);

        return $next($request);
    }
}
