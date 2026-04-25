<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Tenant: {$tenant->nom} ({$tenant->database_name})\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    $db = DB::connection('tenant');

    $tablesToCheck = [
        'commandes', 'bons_commande_fournisseur',
        'ligne_commande', 'ligne_bon_commande_fournisseur'
    ];

    foreach ($tablesToCheck as $table) {
        $exists = collect($db->select("SHOW TABLES"))->contains(fn($t) => array_values((array)$t)[0] === $table);
        if ($exists) {
            $count = $db->table($table)->count();
            echo "  Table '$table': $count records.\n";
        }
    }
}
