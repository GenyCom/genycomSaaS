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
            $tenant = $user->tenants()->find($tenantId);
        } else {
            $tenant = $user->tenants()->first();
        }

        if (!$tenant) {
            return response()->json(['message' => 'Aucun accès à une entreprise SaaS'], 403);
        }

        // Configuration dynamique de la base de données du locataire
        Config::set('database.connections.tenant.database', $tenant->database_name);
        DB::purge('tenant'); // Force la reconnexion avec les paramètres modifiés
        
        // Optionnel : Injecter le tenant dans la requête pour y accéder plus tard
        $request->merge(['current_tenant' => $tenant]);

        return $next($request);
    }
}
