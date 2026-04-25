<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Harmonisation Définitive Tenant: {$tenant->nom}\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    $db = DB::connection('tenant');

    try {
        $db->statement("SET FOREIGN_KEY_CHECKS = 0");

        // 1. S'assurer que ligne_bon_reception existe (nom long standard)
        if (!Schema::connection('tenant')->hasTable('ligne_bon_reception')) {
            echo "  - Création de la table 'ligne_bon_reception'...\n";
            $db->statement("CREATE TABLE ligne_bon_reception LIKE reception_lignes");
        }

        // 2. Transférer les données de 'reception_lignes' (nom court) vers 'ligne_bon_reception'
        if (Schema::connection('tenant')->hasTable('reception_lignes')) {
            $cnt = $db->table('reception_lignes')->count();
            if ($cnt > 0) {
                echo "  - Migration de $cnt lignes de réception...\n";
                // Éviter les doublons si on relance le script
                $db->statement("INSERT IGNORE INTO ligne_bon_reception SELECT * FROM reception_lignes");
            }
        }

        // 3. Transférer les données de 'br' (nom court) vers 'bons_reception' (nom long)
        if (Schema::connection('tenant')->hasTable('br') && Schema::connection('tenant')->hasTable('bons_reception')) {
            $cnt = $db->table('br')->count();
            if ($cnt > 0) {
                echo "  - Migration de $cnt entêtes de réception...\n";
                // On mappe les colonnes si nécessaire (normalement identiques)
                $db->statement("INSERT IGNORE INTO bons_reception SELECT * FROM br");
            }
        }

        // 4. Réinitialiser les Foreign Keys pour utiliser le standard long
        echo "  - Réparation des Foreign Keys...\n";
        
        // Nettoyage FK existantes sur les tables cibles
        $fks = [
            ['ligne_bon_reception', '1'],
            ['bons_reception', '1'],
            ['bons_reception', 'fk_br_commande'],
        ];
        foreach ($fks as $f) {
            try { $db->statement("ALTER TABLE {$f[0]} DROP FOREIGN KEY `{$f[1]}`"); } catch(\Exception $e){}
        }

        // Création des nouvelles FK propres
        try {
            $db->statement("ALTER TABLE bons_reception ADD CONSTRAINT `fk_bons_reception_commande` FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE");
            $db->statement("ALTER TABLE ligne_bon_reception ADD CONSTRAINT `fk_ligne_reception_br` FOREIGN KEY (br_id) REFERENCES bons_reception(id) ON DELETE CASCADE");
            echo "    [OK] FKs 'bons_reception' et 'ligne_bon_reception' réparées.\n";
        } catch (\Exception $e) {
            echo "    [WARN] Erreur FK: " . $e->getMessage() . "\n";
        }

        // 5. Nettoyage des tables zombies
        $zombies = ['br', 'reception_lignes'];
        foreach ($zombies as $z) {
            if (Schema::connection('tenant')->hasTable($z)) {
                echo "  - Suppression table zombie: $z\n";
                $db->statement("DROP TABLE $z");
            }
        }

        $db->statement("SET FOREIGN_KEY_CHECKS = 1");
        echo "  [SUCCESS] Harmonisation terminée.\n";

    } catch (\Throwable $e) {
        $db->statement("SET FOREIGN_KEY_CHECKS = 1");
        echo "  [ERROR] " . $e->getMessage() . "\n";
    }
}
