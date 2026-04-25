<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    if ($tenant->nom !== 'jomia') continue;
    
    echo ">>> Tenant: {$tenant->nom} ({$tenant->database_name})\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    DB::reconnect('tenant');

    $db = DB::connection('tenant');
    
    echo "Tables présentes :\n";
    $tables = $db->select("SHOW TABLES");
    foreach ($tables as $t) {
        $table_name = array_values((array)$t)[0];
        echo " - $table_name\n";
    }

    echo "\nStructure de 'bons_reception' :\n";
    $schema = $db->select("SHOW CREATE TABLE bons_reception")[0]->{'Create Table'};
    echo $schema . "\n";

    if (Schema::connection('tenant')->hasTable('commandes')) {
        echo "\nStructure de 'commandes' :\n";
        $schema = $db->select("SHOW CREATE TABLE commandes")[0]->{'Create Table'};
        echo $schema . "\n";
    }

    if (Schema::connection('tenant')->hasTable('bons_commande_fournisseur')) {
        echo "\nStructure de 'bons_commande_fournisseur' :\n";
        $schema = $db->select("SHOW CREATE TABLE bons_commande_fournisseur")[0]->{'Create Table'};
        echo $schema . "\n";
    }
}
