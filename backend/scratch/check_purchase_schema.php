<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// On suppose qu'on peut se connecter à un tenant pour tester
$tenantId = 1; // Ajuster si besoin
$tenant = \App\Models\Tenant::find($tenantId);

if ($tenant) {
    \App\Services\TenantProvisioningService::configureConnection($tenant);
    
    $tables = ['factures_achats', 'br', 'br_lignes', 'br_facture'];
    foreach ($tables as $table) {
        if (Schema::connection('tenant')->hasTable($table)) {
            echo "Table: $table\n";
            $columns = Schema::connection('tenant')->getColumnListing($table);
            foreach ($columns as $col) {
                echo "  - $col\n";
            }
        } else {
            echo "Table: $table (NOT FOUND)\n";
        }
    }
} else {
    echo "Tenant not found\n";
}
