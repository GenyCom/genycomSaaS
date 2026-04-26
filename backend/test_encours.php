<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = App\Models\Tenant::all();
foreach ($tenants as $tenant) {
    if (!$tenant->database_name) continue;
    \Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $tenant->database_name);
    \Illuminate\Support\Facades\DB::purge('tenant');
    
    $clients = App\Models\Client::with(['factures.etat'])->get();
    foreach($clients as $c) {
        $c->recalculerEncours();
        echo "Client: " . $c->societe . " - Encours: " . $c->montant_rest_du . "\n";
        foreach($c->factures as $f) {
            echo "  - Facture " . $f->numero . " | Restant: " . $f->montant_restant . " | Etat: " . ($f->etat ? $f->etat->code : "NULL") . "\n";
        }
    }
}
