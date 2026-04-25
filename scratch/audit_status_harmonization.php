<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');
$db = DB::connection('tenant');

$tables = ['bons_livraison', 'projets', 'inventaires'];

foreach ($tables as $table) {
    if (Schema::connection('tenant')->hasTable($table)) {
        echo "Structure of '$table':\n";
        print_r($db->select("DESCRIBE $table"));
        echo "\n";
    } else {
        echo "Table '$table' MISSING.\n";
    }
}
