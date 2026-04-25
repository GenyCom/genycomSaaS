<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');
$db = DB::connection('tenant');

echo "--- COMMANDES ID 1 ---\n";
print_r($db->table('commandes')->find(1));

echo "\n--- BONS_COMMANDE_FOURNISSEUR ID 1 ---\n";
print_r($db->table('bons_commande_fournisseur')->find(1));
