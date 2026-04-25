<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

$tenant = \App\Models\Tenant::first();
if (!$tenant) die("No tenant.\n");

Config::set('database.connections.tenant.database', $tenant->database_name);
DB::purge('tenant');

echo "DB: " . $tenant->database_name . "\n";

$tables = DB::connection('tenant')->select('SHOW TABLES');
foreach ($tables as $t) {
    $tableName = array_values((array)$t)[0];
    echo "\n[$tableName]\n";
    $cols = DB::connection('tenant')->select("SHOW COLUMNS FROM $tableName");
    foreach ($cols as $c) {
        echo "  - " . $c->Field . " (" . $c->Type . ")\n";
    }
}
