<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

// Obtenir un tenant existant
$tenant = \App\Models\Tenant::first();
if (!$tenant) {
    die("Aucun tenant trouvé dans la base centrale.\n");
}

echo "Utilisation du tenant : " . $tenant->nom . " (DB: " . $tenant->database_name . ")\n";

Config::set('database.connections.tenant.database', $tenant->database_name);
DB::purge('tenant');

$tables = [
    'bons_reception', 
    'ligne_bon_reception', 
    'dettes_fournisseur', 
    'stocks', 
    'mouvements_stock', 
    'bons_commande_fournisseur',
    'ligne_bon_commande_fournisseur'
];

foreach ($tables as $table) {
    echo "\n--- Colonnes pour $table ---\n";
    try {
        $columns = DB::connection('tenant')->select("SHOW COLUMNS FROM $table");
        foreach ($columns as $column) {
            echo "  " . $column->Field . "\n";
        }
    } catch (\Exception $e) {
        echo "  ERREUR : " . $e->getMessage() . "\n";
    }
}
