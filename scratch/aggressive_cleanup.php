<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Nettoyage final Tenant: {$tenant->nom}\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    $db = DB::connection('tenant');

    try {
        $db->statement("SET FOREIGN_KEY_CHECKS = 0");
        
        $obsoleteTables = [
            'ligne_bon_commande_fournisseur',
            'bons_commande_fournisseur',
            'bcf_lignes',
            'bcf'
        ];

        foreach ($obsoleteTables as $tbl) {
            if (Schema::connection('tenant')->hasTable($tbl)) {
                echo "  - Suppression: $tbl\n";
                $db->statement("DROP TABLE $tbl");
            }
        }

        $db->statement("SET FOREIGN_KEY_CHECKS = 1");
        echo "  [SUCCESS] Nettoyage complet.\n";

    } catch (\Throwable $e) {
        $db->statement("SET FOREIGN_KEY_CHECKS = 1");
        echo "  [ERROR] " . $e->getMessage() . "\n";
    }
}
