<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo "Processing tenant: {$tenant->id} - {$tenant->database_name}\n";
    Config::set('database.connections.tenant.database', $tenant->database_name);
    DB::purge('tenant');
    DB::reconnect('tenant');
    
    try {
        // Vérifier si la table existe
        $tableExists = DB::connection('tenant')->select("SHOW TABLES LIKE 'bons_livraison'");
        if (!$tableExists) {
            echo "Table bons_livraison does not exist for tenant {$tenant->id}. Skipping.\n";
            continue;
        }

        // Ajouter colonnes à bons_livraison
        DB::connection('tenant')->statement("ALTER TABLE `bons_livraison` ADD COLUMN IF NOT EXISTS `projet_id` BIGINT UNSIGNED NULL AFTER `client_id` ");
        DB::connection('tenant')->statement("ALTER TABLE `bons_livraison` ADD COLUMN IF NOT EXISTS `total_ht` DECIMAL(24,2) DEFAULT 0.00 ");
        DB::connection('tenant')->statement("ALTER TABLE `bons_livraison` ADD COLUMN IF NOT EXISTS `total_tva` DECIMAL(24,2) DEFAULT 0.00 ");
        DB::connection('tenant')->statement("ALTER TABLE `bons_livraison` ADD COLUMN IF NOT EXISTS `total_ttc` DECIMAL(24,2) DEFAULT 0.00 ");
        DB::connection('tenant')->statement("ALTER TABLE `bons_livraison` ADD COLUMN IF NOT EXISTS `total_remise` DECIMAL(24,2) DEFAULT 0.00 ");
        DB::connection('tenant')->statement("ALTER TABLE `bons_livraison` ADD COLUMN IF NOT EXISTS `devise_id` BIGINT UNSIGNED NULL ");

        // Ajouter colonnes à ligne_bon_livraison
        DB::connection('tenant')->statement("ALTER TABLE `ligne_bon_livraison` ADD COLUMN IF NOT EXISTS `prix_unitaire` DECIMAL(24,4) DEFAULT 0.0000 ");
        DB::connection('tenant')->statement("ALTER TABLE `ligne_bon_livraison` ADD COLUMN IF NOT EXISTS `taux_tva` DECIMAL(5,3) DEFAULT 0.000 ");
        DB::connection('tenant')->statement("ALTER TABLE `ligne_bon_livraison` ADD COLUMN IF NOT EXISTS `montant_ht` DECIMAL(24,2) DEFAULT 0.00 ");
        DB::connection('tenant')->statement("ALTER TABLE `ligne_bon_livraison` ADD COLUMN IF NOT EXISTS `montant_tva` DECIMAL(24,2) DEFAULT 0.00 ");
        DB::connection('tenant')->statement("ALTER TABLE `ligne_bon_livraison` ADD COLUMN IF NOT EXISTS `montant_ttc` DECIMAL(24,2) DEFAULT 0.00 ");

        echo "Success for tenant {$tenant->id}\n";
    } catch (\Exception $e) {
        echo "Error for tenant {$tenant->id}: " . $e->getMessage() . "\n";
    }
}
echo "Migration complete.\n";
