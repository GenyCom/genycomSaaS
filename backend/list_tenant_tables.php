<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

$tenant = \App\Models\Tenant::first();
Config::set('database.connections.tenant.database', $tenant->database_name);
DB::purge('tenant');

$tables = DB::connection('tenant')->select('SHOW TABLES');
foreach ($tables as $t) {
    echo array_values((array)$t)[0] . "\n";
}
