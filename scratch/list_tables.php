<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tenant = DB::table('tenants')->first();
config(['database.connections.tenant.database' => $tenant->database_name]);
DB::purge('tenant');
$tables = DB::connection('tenant')->select('SHOW TABLES');
foreach($tables as $t) {
    echo array_values((array)$t)[0] . "\n";
}
