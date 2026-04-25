<?php
/**
 * Migration massive : Snapshot des taux de change (V2 Robuste)
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$tablesToUpdate = [
    'devis',
    'factures',
    'bons_commande_client',
    'bons_commande_fournisseur',
    'bons_livraison',
    'bons_reception',
    'factures_achats',
    'avoirs_client',
    'avoirs_fournisseur',
    'contrats',
    'depenses',
    'dettes_fournisseur'
];

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo "--- Mise à jour du tenant: {$tenant->domain} ({$tenant->database_name}) ---\n";
    
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    
    try {
        foreach ($tablesToUpdate as $table) {
            if (!Schema::connection('tenant')->hasTable($table)) {
                echo "Table [{$table}] absente.\n";
                continue;
            }

            Schema::connection('tenant')->table($table, function (Blueprint $t) use ($table) {
                // Check if devise_id exists
                if (!Schema::connection('tenant')->hasColumn($table, 'devise_id')) {
                    // Si tenant_id existe, on met après, sinon après id, sinon au début
                    if (Schema::connection('tenant')->hasColumn($table, 'tenant_id')) {
                        $t->bigInteger('devise_id')->unsigned()->nullable()->after('tenant_id');
                    } else if (Schema::connection('tenant')->hasColumn($table, 'id')) {
                        $t->bigInteger('devise_id')->unsigned()->nullable()->after('id');
                    } else {
                        $t->bigInteger('devise_id')->unsigned()->nullable();
                    }
                }
                
                // Add taux_change_document if missing
                if (!Schema::connection('tenant')->hasColumn($table, 'taux_change_document')) {
                    $t->decimal('taux_change_document', 24, 6)->default(1.0)->after('devise_id');
                }
            });
            echo "Table [{$table}] : OK.\n";
        }

        // 2. Initialiser les valeurs existantes
        $devisePrincipale = DB::connection('tenant')->table('devise')
            ->where('is_principale', true)
            ->first();
            
        if ($devisePrincipale) {
            foreach ($tablesToUpdate as $table) {
                if (!Schema::connection('tenant')->hasTable($table)) continue;

                // Si devise_id est null, on met la devise principale
                DB::connection('tenant')->table($table)
                    ->whereNull('devise_id')
                    ->update([
                        'devise_id' => $devisePrincipale->id,
                        'taux_change_document' => 1.0
                    ]);
                
                // Pour ceux qui ont déjà une devise, on tente de récupérer le taux actuel de cette devise
                $devises = DB::connection('tenant')->table('devise')->get();
                foreach ($devises as $d) {
                    DB::connection('tenant')->table($table)
                        ->where('devise_id', $d->id)
                        ->where('taux_change_document', 1.0) // On ne touche que si c'est la valeur par défaut
                        ->update(['taux_change_document' => $d->taux_change]);
                }
            }
        }
        
    } catch (\Exception $e) {
        echo "ERREUR sur le tenant {$tenant->domain}: " . $e->getMessage() . "\n";
    }
}

echo "Migration terminée.\n";
