<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    if ($tenant->nom !== 'jomia') continue;
    
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    DB::reconnect('tenant');

    $etats = DB::connection('tenant')->table('etat_document')
        ->where('type_document', 'devis')
        ->get();
    echo "États pour 'devis' :\n";
    foreach ($etats as $e) {
        echo json_encode($e) . "\n";
    }
}
