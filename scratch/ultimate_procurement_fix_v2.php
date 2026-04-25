<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Réparation Harmonisation Tenant: {$tenant->nom}\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    $db = DB::connection('tenant');

    try {
        $db->statement("SET FOREIGN_KEY_CHECKS = 0");

        // 1. S'assurer que ligne_bon_reception est correcte
        if (Schema::connection('tenant')->hasTable('reception_lignes')) {
            if (!Schema::connection('tenant')->hasTable('ligne_bon_reception')) {
                echo "  - Création 'ligne_bon_reception' via copie...\n";
                $db->statement("CREATE TABLE ligne_bon_reception LIKE reception_lignes");
            } else {
                echo "  - Alignement colonnes 'ligne_bon_reception'...\n";
                // On s'assure d'avoir les colonnes techniques si absentes
                $cols = Schema::connection('tenant')->getColumnListing('ligne_bon_reception');
                if (!in_array('created_at', $cols)) $db->statement("ALTER TABLE ligne_bon_reception ADD COLUMN created_at TIMESTAMP NULL");
                if (!in_array('updated_at', $cols)) $db->statement("ALTER TABLE ligne_bon_reception ADD COLUMN updated_at TIMESTAMP NULL");
                if (!in_array('deleted_at', $cols)) $db->statement("ALTER TABLE ligne_bon_reception ADD COLUMN deleted_at TIMESTAMP NULL");
                if (!in_array('tenant_id', $cols))  $db->statement("ALTER TABLE ligne_bon_reception ADD COLUMN tenant_id BIGINT UNSIGNED NULL");
            }

            // Migration des données avec INSERT IGNORE
            $db->statement("INSERT IGNORE INTO ligne_bon_reception (id, br_id, produit_id, designation, quantite_commandee, quantite_recue, prix_unitaire, ordre, tenant_id, created_at, updated_at, deleted_at) 
                            SELECT id, br_id, produit_id, designation, quantite_commandee, quantite_recue, prix_unitaire, ordre, tenant_id, created_at, updated_at, deleted_at FROM reception_lignes");
        }

        // 2. Réparation des FKs (Redondant mais sûr)
        echo "  - Création des FKs standard...\n";
        try {
            $db->statement("ALTER TABLE ligne_bon_reception ADD CONSTRAINT `fk_ligne_reception_br` FOREIGN KEY (br_id) REFERENCES bons_reception(id) ON DELETE CASCADE");
            $db->statement("ALTER TABLE bons_reception ADD CONSTRAINT `fk_bons_reception_commande` FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE");
            echo "    [OK] FKs OK.\n";
        } catch (\Exception $e) {
            echo "    [NOTE] FK déjà présente ou erreur ignorée: " . $e->getMessage() . "\n";
        }

        // 3. Suppression zombies
        $zombies = ['br', 'reception_lignes'];
        foreach ($zombies as $z) {
            if (Schema::connection('tenant')->hasTable($z) && Schema::connection('tenant')->hasTable('ligne_bon_reception')) {
               $db->statement("DROP TABLE $z");
            }
        }

        $db->statement("SET FOREIGN_KEY_CHECKS = 1");
        echo "  [SUCCESS] Tenant réparé.\n";

    } catch (\Throwable $e) {
        $db->statement("SET FOREIGN_KEY_CHECKS = 1");
        echo "  [ERROR] " . $e->getMessage() . "\n";
    }
}
