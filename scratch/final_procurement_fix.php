<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Traitement Tenant: {$tenant->nom} ({$tenant->database_name})\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    $db = DB::connection('tenant');

    try {
        // 1. Gérer bons_reception
        if (Schema::connection('tenant')->hasTable('bons_reception')) {
            echo "  - Mise à jour contraintes 'bons_reception'...\n";
            // On essaie de supprimer la contrainte '1' (commande_id) si elle existe et pointe sur bons_commande_fournisseur
            try {
                $db->statement("ALTER TABLE bons_reception DROP FOREIGN KEY `1` ");
            } catch (\Exception $e) {
                echo "    (Note: FK `1` non trouvée ou déjà supprimée)\n";
            }
            
            // Recréer la FK pointant vers 'commandes'
            try {
                $db->statement("ALTER TABLE bons_reception ADD CONSTRAINT `fk_br_commande` FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE");
                echo "    [OK] FK 'fk_br_commande' créée.\n";
            } catch (\Exception $e) {
                echo "    [WARN] Erreur création FK BR: " . $e->getMessage() . "\n";
            }
        }

        // 2. Gérer dettes_fournisseur
        if (Schema::connection('tenant')->hasTable('dettes_fournisseur')) {
            echo "  - Mise à jour contraintes 'dettes_fournisseur'...\n";
            try {
                $db->statement("ALTER TABLE dettes_fournisseur DROP FOREIGN KEY `2` ");
            } catch (\Exception $e) {
                echo "    (Note: FK `2` non trouvée ou déjà supprimée)\n";
            }
            
            try {
                $db->statement("ALTER TABLE dettes_fournisseur ADD CONSTRAINT `fk_dette_commande` FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE");
                echo "    [OK] FK 'fk_dette_commande' créée.\n";
            } catch (\Exception $e) {
                echo "    [WARN] Erreur création FK Dette: " . $e->getMessage() . "\n";
            }
        }

        // 3. Suppression des tables obsolètes
        $obsoleteTables = ['bons_commande_fournisseur', 'ligne_bon_commande_fournisseur', 'bcf', 'bcf_lignes'];
        foreach ($obsoleteTables as $tbl) {
            if (Schema::connection('tenant')->hasTable($tbl)) {
                echo "  - Suppression de la table obsolète: $tbl...\n";
                // Avant de supprimer ligne_bon_commande_fournisseur, on doit virer ses FK si elles existent
                if ($tbl === 'ligne_bon_commande_fournisseur') {
                    try { $db->statement("ALTER TABLE ligne_bon_commande_fournisseur DROP FOREIGN KEY `1` "); } catch(\Exception $e){}
                }
                $db->statement("DROP TABLE $tbl");
            }
        }

        echo "  [SUCCESS] Migration terminée pour {$tenant->nom}.\n";

    } catch (\Throwable $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n";
    }
}
