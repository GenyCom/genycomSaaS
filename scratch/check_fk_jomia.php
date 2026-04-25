<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');
$db = DB::connection('tenant');

echo "STRUCT: bons_reception\n";
echo $db->select("SHOW CREATE TABLE bons_reception")[0]->{'Create Table'} . "\n\n";

echo "STRUCT: commandes\n";
if (collect($db->select("SHOW TABLES"))->contains(fn($t) => array_values((array)$t)[0] === 'commandes')) {
    echo $db->select("SHOW CREATE TABLE commandes")[0]->{'Create Table'} . "\n\n";
} else {
    echo "TABLE 'commandes' NOT FOUND\n\n";
}

echo "CHECKING IF 'bons_commande_fournisseur' EXISTS:\n";
if (collect($db->select("SHOW TABLES"))->contains(fn($t) => array_values((array)$t)[0] === 'bons_commande_fournisseur')) {
    echo "TABLE 'bons_commande_fournisseur' EXISTS\n";
} else {
    echo "TABLE 'bons_commande_fournisseur' MISSING\n";
}
