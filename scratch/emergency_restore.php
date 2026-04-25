<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Script de RESTAURATION d'urgence.
 * Annule les renommages de tables qui ont causé la rupture des liaisons Eloquent.
 */

require dirname(__DIR__) . '/backend/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Début de la restauration du schéma...\n";

try {
    $tenants = DB::connection('central')->table('tenants')->get();

    foreach ($tenants as $tenant) {
        $dbName = $tenant->database_name;
        echo ">>> Restauration pour le tenant : {$tenant->domain} (BDD: {$dbName})\n";

        if (!$dbName) continue;

        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        $schema = Schema::connection('tenant');

        // 1. Inversion des renommages Achats / Ventes
        $tablesToRestore = [
            'bcf' => 'commandes',
            'bcf_lignes' => 'ligne_commande',
            'br' => 'bons_reception',
            'br_lignes' => 'reception_lignes',
        ];

        foreach ($tablesToRestore as $new => $old) {
            if ($schema->hasTable($new) && !$schema->hasTable($old)) {
                echo "    [<] Rétablissement de {$new} vers {$old}...\n";
                $schema->rename($new, $old);
            }
        }

        // NOTE : On garde factures_achats car c'est une nouvelle table qui ne devrait pas gêner.
        // On garde tenant_id dans alertes_stock car c'est un correctif de sécurité/multi-tenant.
    }

    echo "\nTerminé ! Les tables d'origine ont été rétablies.\n";

} catch (\Exception $e) {
    echo "\nERREUR : " . $e->getMessage() . "\n";
}
