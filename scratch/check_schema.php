<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Tenant: {$tenant->id} (BDD: {$tenant->database_name})\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    DB::reconnect('tenant');

    if (Schema::connection('tenant')->hasTable('ligne_commande')) {
        $columns = Schema::connection('tenant')->getColumnListing('ligne_commande');
        echo "    Table 'ligne_commande' colonnes : " . implode(', ', $columns) . "\n";
    } else {
        echo "    Table 'ligne_commande' NON TROUVÉE.\n";
    }
}
