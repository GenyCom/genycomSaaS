<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

foreach (Tenant::all() as $tenant) {
    echo "Updating ALL status labels to uppercase for tenant: {$tenant->database_name}\n";
    Config::set('database.connections.tenant.database', $tenant->database_name);
    DB::purge('tenant');

    $etats = DB::connection('tenant')->table('etat_document')->get();
    foreach ($etats as $etat) {
        $newLibelle = mb_strtoupper($etat->libelle, 'UTF-8');
        DB::connection('tenant')->table('etat_document')
            ->where('id', $etat->id)
            ->update(['libelle' => $newLibelle]);
    }
}

echo "Done!\n";
