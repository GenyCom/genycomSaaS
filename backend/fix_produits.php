<?php
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = Tenant::all();
foreach ($tenants as $t) {
    echo "Updating database for products: {$t->database_name}...\n";
    Config::set('database.connections.tenant.database', $t->database_name);
    DB::purge('tenant');
    
    $tables = [
        'produits' => 'AFTER id',
        'tarifs_produit' => 'AFTER id',
        'produit_fini' => 'AFTER id',
        'nomenclature_produit' => 'AFTER id',
        'historique_produit' => 'AFTER id'
    ];

    foreach ($tables as $table => $after) {
        try {
            DB::connection('tenant')->statement("ALTER TABLE `{$table}` ADD COLUMN IF NOT EXISTS tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1 {$after}");
            echo "  ✅ Column tenant_id added to {$table}\n";
        } catch (\Exception $e) {
            echo "  ❌ Error for {$table} in {$t->database_name}: " . $e->getMessage() . "\n";
        }
    }

    // Fix unique key for produits
    try {
        DB::connection('tenant')->statement("ALTER TABLE produits DROP INDEX IF EXISTS uk_ref_tenant, ADD UNIQUE KEY uk_ref_tenant (tenant_id, reference)");
        echo "  ✅ Unique key uk_ref_tenant updated for produits\n";
    } catch (\Exception $e) {
        echo "  ❌ Error updating unique key for produits: " . $e->getMessage() . "\n";
    }
}
echo "FINISH.\n";
