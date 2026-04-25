<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Harmonisation V3 Tenant: {$tenant->nom}\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    $db = DB::connection('tenant');

    try {
        $db->statement("SET FOREIGN_KEY_CHECKS = 0");

        // 1. Uniformiser ligne_bon_reception
        // On la recrée proprement si nécessaire ou on la corrige
        echo "  - Préparation 'ligne_bon_reception'...\n";
        if (!Schema::connection('tenant')->hasTable('ligne_bon_reception')) {
             $db->statement("CREATE TABLE ligne_bon_reception (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT UNSIGNED NULL,
                br_id BIGINT UNSIGNED NOT NULL,
                produit_id BIGINT UNSIGNED NULL,
                designation VARCHAR(255) NOT NULL,
                quantite_commandee DECIMAL(24,2) DEFAULT 0,
                quantite_recue DECIMAL(24,2) DEFAULT 0,
                prix_unitaire DECIMAL(24,2) DEFAULT 0,
                ordre INT DEFAULT 0,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
             ) ENGINE=InnoDB");
        } else {
            // S'assurer que la colonne est br_id
            if (Schema::connection('tenant')->hasColumn('ligne_bon_reception', 'bon_reception_id')) {
                $db->statement("ALTER TABLE ligne_bon_reception CHANGE bon_reception_id br_id BIGINT UNSIGNED NOT NULL");
            }
            // S'assurer qu'on a created_at pour le futur
            if (!Schema::connection('tenant')->hasColumn('ligne_bon_reception', 'created_at')) {
                $db->statement("ALTER TABLE ligne_bon_reception ADD COLUMN created_at TIMESTAMP NULL");
            }
        }

        // 2. Transférer les données de reception_lignes
        if (Schema::connection('tenant')->hasTable('reception_lignes')) {
            $cnt = $db->table('reception_lignes')->count();
            if ($cnt > 0) {
                echo "  - Transfert de $cnt lignes...\n";
                // Mapping manuel pour éviter tout mismatch
                $db->statement("INSERT IGNORE INTO ligne_bon_reception (id, tenant_id, br_id, produit_id, designation, quantite_commandee, quantite_recue, prix_unitaire, ordre)
                                SELECT id, tenant_id, br_id, produit_id, designation, quantite_commandee, quantite_recue, prix_unitaire, ordre FROM reception_lignes");
            }
        }

        // 3. Réparer les FKs
        echo "  - Réparation des FKs...\n";
        try {
            $db->statement("ALTER TABLE ligne_bon_reception ADD CONSTRAINT `fk_ligne_reception_br` FOREIGN KEY (br_id) REFERENCES bons_reception(id) ON DELETE CASCADE");
            $db->statement("ALTER TABLE bons_reception ADD CONSTRAINT `fk_bons_reception_commande` FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE");
        } catch (\Exception $e) {
            echo "    [NOTE] FK déjà là ou erreur: " . $e->getMessage() . "\n";
        }

        // 4. Nettoyage final
        $zombies = ['br', 'reception_lignes', 'ligne_bon_commande_fournisseur', 'bons_commande_fournisseur'];
        foreach ($zombies as $z) {
            if (Schema::connection('tenant')->hasTable($z)) {
                $db->statement("DROP TABLE $z");
            }
        }

        $db->statement("SET FOREIGN_KEY_CHECKS = 1");
        echo "  [SUCCESS] Harmonisation terminée pour {$tenant->nom}.\n";

    } catch (\Throwable $e) {
        $db->statement("SET FOREIGN_KEY_CHECKS = 1");
        echo "  [ERROR] " . $e->getMessage() . "\n";
    }
}
