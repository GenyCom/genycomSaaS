<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Tenant: {$tenant->id} (BDD: {$tenant->database_name})\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    DB::reconnect('tenant');

    $tables = ['commandes', 'ligne_commande', 'bons_reception', 'reception_lignes'];
    foreach ($tables as $table) {
        if (Schema::connection('tenant')->hasTable($table)) {
           $cols = Schema::connection('tenant')->getColumnListing($table);
           echo "    [OK] {$table} : " . implode(', ', $cols) . "\n";
        } else {
           echo "    [!!] {$table} MANQUANTE\n";
        }
    }
}
