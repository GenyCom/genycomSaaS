<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

$sql = file_get_contents('database/schema/009_contrats_abonnements.sql');

foreach(['genycom_acme', 'genycom_hp'] as $db) {
    Config::set('database.connections.tenant.database', $db);
    DB::purge('tenant');

    try {
        DB::connection('tenant')->unprepared($sql);
        echo "009 Schema injected successfully to $db.\n";
    } catch (\Exception $e) {
        echo "ERROR on $db: " . $e->getMessage() . "\n";
    }
}
