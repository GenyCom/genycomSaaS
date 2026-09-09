<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== DÉBUT DE L'ISOLATION DES RÔLES PAR TENANT ===\n";

    $tenants = DB::connection('central')->table('tenants')->get();

    foreach ($tenants as $tenant) {
        echo "\n--- Traitement du tenant ID {$tenant->id} ({$tenant->nom}) ---\n";

        // Vérifier si des rôles existent déjà pour ce tenant
        $existingTenantRoles = DB::connection('central')->table('roles')->where('tenant_id', $tenant->id)->get();

        if ($existingTenantRoles->count() === 0) {
            // 1. Cloner le rôle 'admin' pour ce tenant
            $adminRoleId = DB::connection('central')->table('roles')->insertGetId([
                'tenant_id'   => $tenant->id,
                'name'        => 'admin',
                'description' => 'Administrateur complet du tenant',
                'is_system'   => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Copier les permissions du rôle 1 vers le nouveau rôle admin
            $adminPerms = DB::connection('central')->table('permission_role')->where('role_id', 1)->get();
            foreach ($adminPerms as $ap) {
                DB::connection('central')->table('permission_role')->insertOrIgnore([
                    'role_id'       => $adminRoleId,
                    'permission_id' => $ap->permission_id,
                ]);
            }
            echo "✔ Rôle 'admin' cloné avec ID {$adminRoleId} (" . $adminPerms->count() . " permissions).\n";

            // 2. Cloner le rôle 'utilisateur' pour ce tenant
            $userRoleId = DB::connection('central')->table('roles')->insertGetId([
                'tenant_id'   => $tenant->id,
                'name'        => 'utilisateur',
                'description' => 'Utilisateur standard',
                'is_system'   => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Copier les permissions du rôle 2 vers le nouveau rôle utilisateur
            $userPerms = DB::connection('central')->table('permission_role')->where('role_id', 2)->get();
            foreach ($userPerms as $up) {
                DB::connection('central')->table('permission_role')->insertOrIgnore([
                    'role_id'       => $userRoleId,
                    'permission_id' => $up->permission_id,
                ]);
            }
            echo "✔ Rôle 'utilisateur' cloné avec ID {$userRoleId} (" . $userPerms->count() . " permissions).\n";

            // 3. Mettre à jour les utilisateurs du tenant dans tenant_user
            DB::connection('central')->table('tenant_user')
                ->where('tenant_id', $tenant->id)
                ->where('role_id', 1)
                ->update(['role_id' => $adminRoleId]);

            DB::connection('central')->table('tenant_user')
                ->where('tenant_id', $tenant->id)
                ->where('role_id', 2)
                ->update(['role_id' => $userRoleId]);

            echo "✔ Réattributions des rôles terminées pour les utilisateurs de {$tenant->nom}.\n";
        } else {
            echo "Le tenant {$tenant->nom} possède déjà " . $existingTenantRoles->count() . " rôle(s) propre(s).\n";
        }
    }

    echo "\n=== FIN DU PATCH AVEC SUCCÈS ===\n";
} catch (\Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
}
