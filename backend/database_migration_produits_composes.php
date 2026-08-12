<?php
use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = Tenant::all();

echo "=== STARTING COMPOSITE PRODUCTS DB MIGRATION ===\n";

foreach ($tenants as $tenant) {
    echo "Processing Tenant: {$tenant->nom} (DB: {$tenant->database_name})...\n";
    try {
        $tenant->configure();
        $tables = ['ligne_devis', 'ligne_facture', 'ligne_bon_livraison'];

        foreach ($tables as $table) {
            echo " - Table `{$table}`:\n";

            // 1. Make produit_id NULLABLE
            DB::connection('tenant')->statement("
                ALTER TABLE `{$table}` MODIFY `produit_id` BIGINT UNSIGNED NULL
            ");
            echo "   ↳ `produit_id` modified to NULLABLE.\n";

            // 2. Add produit_fini_id column and foreign key if not exist
            $hasFiniId = Schema::connection('tenant')->hasColumn($table, 'produit_fini_id');
            if (!$hasFiniId) {
                DB::connection('tenant')->statement("
                    ALTER TABLE `{$table}` 
                    ADD COLUMN `produit_fini_id` BIGINT UNSIGNED NULL AFTER `produit_id`,
                    ADD CONSTRAINT `fk_{$table}_produit_fini` FOREIGN KEY (`produit_fini_id`) REFERENCES `produit_fini`(`id`) ON DELETE SET NULL
                ");
                echo "   ↳ `produit_fini_id` column and foreign key constraint added.\n";
            } else {
                echo "   ↳ `produit_fini_id` already exists.\n";
            }

            // 3. Add is_produit_fini column if not exist
            $hasIsFini = Schema::connection('tenant')->hasColumn($table, 'is_produit_fini');
            if (!$hasIsFini) {
                DB::connection('tenant')->statement("
                    ALTER TABLE `{$table}` 
                    ADD COLUMN `is_produit_fini` TINYINT(1) NOT NULL DEFAULT 0 AFTER `produit_fini_id`
                ");
                echo "   ↳ `is_produit_fini` column added.\n";
            } else {
                echo "   ↳ `is_produit_fini` already exists.\n";
            }
        }
    } catch (\Exception $e) {
        echo " ❌ ERROR on tenant {$tenant->nom}: " . $e->getMessage() . "\n";
    }
    echo "--------------------------------------------------\n";
}
echo "=== DB MIGRATION COMPLETED ===\n";
