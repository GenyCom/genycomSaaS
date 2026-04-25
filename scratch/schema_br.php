<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');

echo DB::connection('tenant')->select('SHOW CREATE TABLE bons_reception')[0]->{'Create Table'} . PHP_EOL;

$fks = DB::connection('tenant')->select("
    SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE table_schema = 'geny_jomia' AND table_name = 'bons_reception' AND REFERENCED_TABLE_NAME IS NOT NULL
");

print_r($fks);
