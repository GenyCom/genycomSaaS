<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_hp']);
DB::purge('tenant');

$tables = [
    'bcf', 'bons_commande_fournisseur',
    'br', 'bons_reception',
    'factures_achats', 'dettes_fournisseur'
];

foreach ($tables as $table) {
    try {
        $count = DB::connection('tenant')->table($table)->count();
        echo "Table '$table' count: $count\n";
    } catch (\Exception $e) {
        echo "Table '$table' does not exist or error: " . $e->getMessage() . "\n";
    }
}
