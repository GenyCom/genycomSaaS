<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');
$db = DB::connection('tenant');

echo "--- TABLES ---\n";
foreach($db->select('SHOW TABLES') as $t) {
    echo array_values((array)$t)[0] . "\n";
}

echo "\n--- CHECKS ---\n";
$tablesToCheck = [
    'commandes', 'bons_commande_fournisseur', 'bcf',
    'ligne_commande', 'ligne_bon_commande_fournisseur', 'bcf_lignes',
    'bons_reception', 'reception_lignes',
    'factures_achats', 'facture_achat_lignes'
];

foreach ($tablesToCheck as $table) {
    $exists = collect($db->select("SHOW TABLES"))->contains(fn($t) => array_values((array)$t)[0] === $table);
    if ($exists) {
        $count = $db->table($table)->count();
        echo "Table '$table' exists with $count records.\n";
    } else {
        echo "Table '$table' MISSING.\n";
    }
}
