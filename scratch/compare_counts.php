<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tenantDb = 'genycom_sohan';
Config::set('database.connections.tenant.database', $tenantDb);
DB::purge('tenant');

try {
    $countNew = DB::connection('tenant')->table('bcf')->count();
} catch (\Exception $e) { $countNew = 'Error (' . $e->getMessage() . ')'; }

try {
    $countOld = DB::connection('tenant')->table('bons_commande_fournisseur')->count();
} catch (\Exception $e) { $countOld = 'Error (' . $e->getMessage() . ')'; }

echo "New BCF: $countNew\nOld BCF: $countOld\n";
