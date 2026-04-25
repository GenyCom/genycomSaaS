<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$tenants = DB::connection('central')->table('tenants')->get(['database_name']);

foreach ($tenants as $tenant) {
    echo "Updating tenant: {$tenant->database_name}...\n";
    try {
        Config::set('database.connections.tenant.database', $tenant->database_name);
        DB::purge('tenant');
        $conn = DB::connection('tenant');

        // Ajouter tenant_id aux tables de lignes si absent
        $tables = ['bcf_lignes', 'br_lignes', 'facture_achat_lignes'];
        foreach ($tables as $table) {
            if (Schema::connection('tenant')->hasTable($table)) {
                if (!Schema::connection('tenant')->hasColumn($table, 'tenant_id')) {
                    $conn->statement("ALTER TABLE `{$table}` ADD COLUMN `tenant_id` BIGINT UNSIGNED DEFAULT 1 AFTER `id` ");
                    echo "  - Column tenant_id added to {$table}\n";
                } else {
                    echo "  - Column tenant_id already exists in {$table}\n";
                }
            } else {
                echo "  - Table {$table} does not exist\n";
            }
        }
    } catch (\Exception $e) {
        echo "  - Error: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
