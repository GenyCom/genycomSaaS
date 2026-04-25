<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');
$db = DB::connection('tenant');

$tables = $db->select("SHOW TABLES");
foreach ($tables as $t) {
    $table = array_values((array)$t)[0];
    
    $fks = $db->select("
        SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE table_schema = 'geny_jomia' 
          AND table_name = ? 
          AND REFERENCED_TABLE_NAME = 'bons_commande_fournisseur'
    ", [$table]);
    
    if (!empty($fks)) {
        echo "Table: $table\n";
        foreach ($fks as $fk) {
            echo "  Constraint: {$fk->CONSTRAINT_NAME} ({$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME})\n";
        }
    }
}
