<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // SuperAdmin a un accès total sans vérification
        if ($user->is_superadmin) {
            return $next($request);
        }

        $tenant = $request->get('current_tenant') ?: $user->tenant;

        if (!$tenant) {
            return response()->json(['message' => 'Aucun accès à un tenant.'], 403);
        }

        $tenantPivot = DB::connection('central')
            ->table('tenant_user')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$tenantPivot) {
            return response()->json(['message' => 'Accès refusé pour ce tenant.'], 403);
        }

        // Le gérant du tenant a tous les accès
        if ($tenantPivot->is_owner) {
            return $next($request);
        }

        $roleId = $tenantPivot->role_id;
        if (!$roleId) {
            return response()->json(['message' => 'Accès refusé. Aucun rôle n\'est attribué.'], 403);
        }

        // Vérification du rôle 'admin' (accès total)
        $role = DB::connection('central')->table('roles')->where('id', $roleId)->first();
        if ($role && strtolower($role->name) === 'admin') {
            return $next($request);
        }

        // Vérification si la permission est liée au rôle
        $hasPermission = DB::connection('central')
            ->table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permission_role.role_id', $roleId)
            ->where('permissions.name', $permission)
            ->exists();

        if (!$hasPermission) {
            return response()->json([
                'message' => "Accès refusé. Habilitation requise : {$permission}"
            ], 403);
        }

        return $next($request);
    }
}
