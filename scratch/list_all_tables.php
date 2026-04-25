<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    if ($tenant->nom !== 'jomia') continue;
    
    echo ">>> Tenant: {$tenant->id} (BDD: {$tenant->database_name})\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    DB::reconnect('tenant');

    $tables = DB::connection('tenant')->select('SHOW TABLES');
    echo "    Tables :\n";
    foreach ($tables as $t) {
        $name = array_values((array)$t)[0];
        echo "    - {$name}\n";
    }
}
