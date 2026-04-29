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

        // Pas encore authentifié (ex: /api/login) → on laisse passer
        if (!$user) {
            return $next($request);
        }

        // Le SuperAdmin naviguant sur l'espace central n'a pas besoin de base tenant
        if ($user->is_superadmin && $request->is('api/superadmin/*')) {
            return $next($request);
        }

        // Si l'utilisateur n'a pas spécifié de tenant dans la requête (header ou query),
        // on prend le premier tenant auquel il est rattaché.
        $tenantId = $request->header('X-Tenant-ID') ?: $request->tenant_id;

        $tenant = null;
        if ($tenantId) {
            // Un SuperAdmin peut accéder à n'importe quel tenant s'il spécifie l'ID
            if ($user->is_superadmin) {
                $tenant = \App\Models\Tenant::find($tenantId);
            } else {
                $tenant = $user->tenants()->find($tenantId);
            }
        } else {
            $tenant = $user->tenants()->first();

            // Fallback pour SuperAdmin
            if (!$tenant && $user->is_superadmin) {
                $tenant = \App\Models\Tenant::find(2)
                    ?: \App\Models\Tenant::whereHas('users')->first()
                    ?: \App\Models\Tenant::first();
            }
        }

        if (!$tenant) {
            return response()->json(['message' => 'Aucun accès à une entreprise SaaS'], 403);
        }

        // Utiliser la méthode centralisée pour configurer la connexion
        $tenant->configure();

        // Injecter le tenant dans la requête pour y accéder dans les contrôleurs
        $request->merge(['current_tenant' => $tenant]);

        return $next($request);
    }
}