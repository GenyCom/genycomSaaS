<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_hp']);
DB::purge('tenant');

$tables = DB::connection('tenant')->select('SHOW TABLES');
foreach($tables as $t) {
    $arr = (array)$t;
    echo reset($arr) . "\n";
}
