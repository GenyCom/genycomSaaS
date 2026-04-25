<?php
/**
 * Script d'audit des tables de documents pour GenyCom
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$targetTables = [
    'devis',
    'factures',
    'bons_commande_client',
    'commandes', // BCF ?
    'bons_livraison',
    'bons_reception',
    'factures_achats',
    'avoirs_client',
    'avoirs_fournisseur',
    'contrats',
    'depenses',
    'dettes_fournisseur'
];

$results = [];

// On teste sur le premier tenant trouvé (ex: id 12 d'après les logs)
$tenant = DB::table('tenants')->first();
if (!$tenant) {
    echo "Aucun tenant trouvé.\n";
    exit;
}

config(['database.connections.tenant.database' => $tenant->database_name]);
DB::purge('tenant');
DB::reconnect('tenant');

echo "Audit du tenant: {$tenant->domain} (DB: {$tenant->database_name})\n";
echo str_pad("Table", 25) . " | " . str_pad("devise_id", 10) . " | " . str_pad("taux_change", 15) . "\n";
echo str_repeat("-", 60) . "\n";

foreach ($targetTables as $table) {
    if (!Schema::connection('tenant')->hasTable($table)) {
        echo str_pad($table, 25) . " | (Table manquante)\n";
        continue;
    }

    $hasDevise = Schema::connection('tenant')->hasColumn($table, 'devise_id') ? "OUI" : "NON";
    $hasTaux   = Schema::connection('tenant')->hasColumn($table, 'taux_change_document') ? "OUI" : "NON";
    
    echo str_pad($table, 25) . " | " . str_pad($hasDevise, 10) . " | " . str_pad($hasTaux, 15) . "\n";
}
