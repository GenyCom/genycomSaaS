<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tenantDb = 'genycom_sohan';
Config::set('database.connections.tenant.database', $tenantDb);
DB::purge('tenant');

$tables = ['ligne_bon_reception'];

foreach ($tables as $table) {
    echo "\nColumns for $table:\n";
    try {
        $columns = DB::connection('tenant')->select("SHOW COLUMNS FROM $table");
        foreach ($columns as $col) {
            echo " - {$col->Field} ({$col->Type})\n";
        }
    } catch (\Exception $e) {
        echo " Error: " . $e->getMessage() . "\n";
    }
}
