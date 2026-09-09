<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "--- Début du patch pour l'isolation des rôles par tenant ---\n";

    // 1. Ajouter la colonne tenant_id si elle n'existe pas
    if (!Schema::connection('central')->hasColumn('roles', 'tenant_id')) {
        DB::connection('central')->statement("
            ALTER TABLE `roles` 
            ADD COLUMN `tenant_id` BIGINT UNSIGNED NULL AFTER `id`;
        ");
        echo "1. Colonne 'tenant_id' ajoutée à la table 'roles'.\n";

        // Ajouter la clé étrangère vers tenants(id)
        try {
            DB::connection('central')->statement("
                ALTER TABLE `roles` 
                ADD CONSTRAINT `fk_roles_tenant` 
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE;
            ");
            echo "2. Clé étrangère 'fk_roles_tenant' créée.\n";
        } catch (\Exception $exFk) {
            echo "Avertissement Clé Étrangère : " . $exFk->getMessage() . "\n";
        }
    } else {
        echo "1. La colonne 'tenant_id' existe déjà sur la table 'roles'.\n";
    }

    // 2. Retirer l'index UNIQUE global sur le champ 'name'
    $indexes = DB::connection('central')->select("SHOW INDEX FROM roles WHERE Key_name = 'name'");
    if (count($indexes) > 0) {
        DB::connection('central')->statement("ALTER TABLE `roles` DROP INDEX `name`;");
        echo "3. Ancien index UNIQUE global 'name' supprimé.\n";
    } else {
        echo "3. L'index UNIQUE 'name' a déjà été supprimé ou n'existe pas.\n";
    }

    echo "--- Patch SQL exécuté avec succès ! ---\n";
} catch (\Exception $e) {
    echo "ERREUR lors du patch : " . $e->getMessage() . "\n";
}
