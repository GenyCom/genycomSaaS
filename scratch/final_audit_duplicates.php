<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');
$db = DB::connection('tenant');

$mapping = [
    'br' => 'bons_reception',
    'bcf' => 'commandes',
    'reception_lignes' => 'ligne_bon_reception',
    'bcf_lignes' => 'ligne_commande'
];

foreach ($mapping as $short => $long) {
    $hasShort = Schema::connection('tenant')->hasTable($short);
    $hasLong = Schema::connection('tenant')->hasTable($long);
    echo "Check: $short vs $long\n";
    if ($hasShort) echo "  - Short '$short' exists: " . $db->table($short)->count() . " records.\n";
    if ($hasLong)  echo "  - Long  '$long' exists: " . $db->table($long)->count() . " records.\n";
}
