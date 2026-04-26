<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Tenant::find(1);
\Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $tenant->database_name);
\Illuminate\Support\Facades\DB::purge('tenant');

$count = \Illuminate\Support\Facades\DB::connection('tenant')->table('devis')
    ->where(function($q) {
        $q->whereExists(function($sq) {
            $sq->select(\Illuminate\Support\Facades\DB::raw(1))
               ->from('bons_commande_client')
               ->whereRaw('bons_commande_client.devis_id = devis.id');
        })->orWhereExists(function($sq) {
            $sq->select(\Illuminate\Support\Facades\DB::raw(1))
               ->from('factures')
               ->whereRaw('factures.devis_id = devis.id');
        });
    })
    ->update(['etat_id' => 32]);
echo "Updated $count devis to Accepté (ID 32)";
