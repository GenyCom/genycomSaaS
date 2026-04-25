<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    if ($tenant->nom !== 'jomia') continue;
    
    echo ">>> Tenant: {$tenant->id} (BDD: {$tenant->database_name})\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    DB::reconnect('tenant');

    if (Schema::connection('tenant')->hasTable('devis')) {
        $cols = Schema::connection('tenant')->getColumnListing('devis');
        echo "    Colonnes 'devis' : " . implode(', ', $cols) . "\n";
    }

    if (Schema::connection('tenant')->hasTable('etat_document')) {
        $etats = DB::connection('tenant')->table('etat_document')
            ->where('type_document', 'devis')
            ->get();
        echo "    États 'devis' :\n";
        foreach ($etats as $e) {
            echo "    - [{$e->code}] {$e->libelle} (Couleur: {$e->couleur}, ID: {$e->id})\n";
        }
    }
}
