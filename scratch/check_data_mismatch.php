<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');
$db = DB::connection('tenant');

echo "DATA in 'commandes' (ID 7):\n";
$c = $db->table('commandes')->where('id', 7)->first();
print_r($c);

echo "\nDATA in 'bons_commande_fournisseur' (ID 7):\n";
if (Schema::connection('tenant')->hasTable('bons_commande_fournisseur')) {
    $bcf = $db->table('bons_commande_fournisseur')->where('id', 7)->first();
    print_r($bcf);
} else {
    echo "TABLE 'bons_commande_fournisseur' MISSING\n";
}
