<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

foreach (Tenant::all() as $tenant) {
    echo "Updating BCC statuses for tenant: {$tenant->database_name}\n";
    Config::set('database.connections.tenant.database', $tenant->database_name);
    DB::purge('tenant');

    DB::connection('tenant')->table('etat_document')
        ->where('type_document', 'bcc')
        ->where('code', 'BROUILLON')
        ->update(['libelle' => 'BROUILLON']);

    DB::connection('tenant')->table('etat_document')
        ->where('type_document', 'bcc')
        ->where('code', 'VALIDE')
        ->update(['libelle' => 'VALIDÉ']);

    DB::connection('tenant')->table('etat_document')
        ->where('type_document', 'bcc')
        ->where('code', 'LIVRE')
        ->update(['libelle' => 'LIVRÉ']);

    DB::connection('tenant')->table('etat_document')
        ->where('type_document', 'bcc')
        ->where('code', 'FACTURE')
        ->update(['libelle' => 'FACTURÉ']);

    DB::connection('tenant')->table('etat_document')
        ->where('type_document', 'bcc')
        ->where('code', 'ANNULE')
        ->update(['libelle' => 'ANNULÉ']);
}

echo "Done!\n";
