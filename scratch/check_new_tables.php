<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tenantDb = 'genycom_sohan';
Config::set('database.connections.tenant.database', $tenantDb);
DB::purge('tenant');

$tables = ['bcf', 'br', 'factures_achats'];
echo "Checking for new tables in $tenantDb:\n";

foreach ($tables as $table) {
    try {
        $res = DB::connection('tenant')->select("SHOW TABLES LIKE '$table'");
        if (!empty($res)) {
            echo " [OK] Table '$table' exists.\n";
        } else {
            echo " [MISSING] Table '$table' does not exist.\n";
        }
    } catch (\Exception $e) {
        echo " [ERROR] Table '$table': " . $e->getMessage() . "\n";
    }
}
