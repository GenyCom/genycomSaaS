<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    if ($tenant->nom !== 'jomia') continue;
    
    echo ">>> Tenant: {$tenant->nom} ({$tenant->database_name})\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    $db = DB::connection('tenant');

    $cntCommandes = $db->table('commandes')->count();
    echo "Total in 'commandes': $cntCommandes\n";

    if (Schema::connection('tenant')->hasTable('bons_commande_fournisseur')) {
        $cntBCF = $db->table('bons_commande_fournisseur')->count();
        echo "Total in 'bons_commande_fournisseur': $cntBCF\n";
    } else {
        echo "Table 'bons_commande_fournisseur' does not exist.\n";
    }
}
