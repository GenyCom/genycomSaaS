<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

\Illuminate\Support\Facades\Config::set('database.connections.tenant.database', 'geny_hp');
\Illuminate\Support\Facades\DB::purge('tenant');

$facture = \Illuminate\Support\Facades\DB::connection('tenant')->table('factures')
    ->where('numero', 'FAC-2026-0001')
    ->first();

if ($facture) {
    echo "NUMERO: " . $facture->numero . "\n";
    echo "EST_REGLEE: " . $facture->est_reglee . "\n";
    echo "DATE_ECHEANCE: " . $facture->date_echeance . "\n";
    echo "MONTANT_REGLE: " . $facture->montant_regle . "\n";
    echo "TOTAL_TTC: " . $facture->total_ttc . "\n";
    echo "TODAY: " . date('Y-m-d') . "\n";
    echo "OVERDUE? " . ($facture->date_echeance < date('Y-m-d') ? 'YES' : 'NO') . "\n";
} else {
    echo "Facture non trouvée.\n";
}
