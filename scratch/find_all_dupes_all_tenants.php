<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Tenant: {$tenant->nom}\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    $db = DB::connection('tenant');

    $dupes = [
        ['br', 'bons_reception'],
        ['reception_lignes', 'ligne_bon_reception'],
        ['bcf', 'commandes'], // Déjà vu, mais à confirmer
    ];

    foreach ($dupes as $pair) {
        $found = [];
        foreach ($pair as $tbl) {
            if (Schema::connection('tenant')->hasTable($tbl)) $found[] = $tbl;
        }
        if (count($found) > 1) {
            echo "  [DUPLICATE] " . implode(' & ', $found) . "\n";
            foreach ($found as $tbl) {
                echo "    - $tbl: " . $db->table($tbl)->count() . " records.\n";
            }
        }
    }
}
