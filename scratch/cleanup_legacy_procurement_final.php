<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_hp']);
DB::purge('tenant');

$conn = DB::connection('tenant');

echo "Cleaning 'geny_hp' database (Safe mode)...\n";
$conn->statement('SET FOREIGN_KEY_CHECKS=0');

$legacyTables = [
    'ligne_bon_commande_fournisseur', 'bons_commande_fournisseur',
    'ligne_bon_reception', 'bons_reception',
    'dettes_fournisseur',
    'ligne_avoir_fournisseur', 'avoirs_fournisseur',
    'commandes'
];

foreach ($legacyTables as $table) {
    try {
        $conn->statement("DROP TABLE IF EXISTS `$table` CASCADE");
        echo "- Dropped legacy table: $table\n";
    } catch (\Exception $e) {
        echo "- Error dropping $table: " . $e->getMessage() . "\n";
    }
}

$conn->statement('SET FOREIGN_KEY_CHECKS=1');
echo "SUCCESS!\n";
