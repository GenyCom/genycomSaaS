<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');
$db = DB::connection('tenant');

function showCols($db, $table) {
    if (!Schema::connection('tenant')->hasTable($table)) {
        echo "Table $table MISSING\n";
        return;
    }
    echo "COLUMNS for $table:\n";
    $cols = $db->select("DESCRIBE $table");
    foreach ($cols as $c) {
        echo " - {$c->Field}\n";
    }
}

showCols($db, 'reception_lignes');
echo "\n";
showCols($db, 'ligne_bon_reception');
