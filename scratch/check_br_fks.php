<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');
$db = DB::connection('tenant');

echo "--- TABLE reception_lignes ---\n";
echo $db->select("SHOW CREATE TABLE reception_lignes")[0]->{'Create Table'} . "\n\n";

echo "--- TABLES LIST ---\n";
foreach($db->select('SHOW TABLES') as $t) {
    echo array_values((array)$t)[0] . "\n";
}
