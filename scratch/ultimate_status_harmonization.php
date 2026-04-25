<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Harmonisation Définitive Tenant: {$tenant->nom} ({$tenant->database_name})\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    $db = DB::connection('tenant');

    try {
        // 1. Harmonisation structurelle de etat_document
        if (Schema::connection('tenant')->hasTable('etat_document')) {
            echo "  - Vérification structure etat_document...\n";
            // Renommer couleur_bg -> color
            if (Schema::connection('tenant')->hasColumn('etat_document', 'couleur_bg') && !Schema::connection('tenant')->hasColumn('etat_document', 'color')) {
                echo "    - Renommage couleur_bg -> color\n";
                $db->statement("ALTER TABLE etat_document CHANGE couleur_bg color VARCHAR(50)");
            }
            // Ajouter badge_class si absent
            if (!Schema::connection('tenant')->hasColumn('etat_document', 'badge_class')) {
                echo "    - Ajout badge_class\n";
                $db->statement("ALTER TABLE etat_document ADD COLUMN badge_class VARCHAR(100) NULL");
            }
            // Ajouter is_default si absent
            if (!Schema::connection('tenant')->hasColumn('etat_document', 'is_default')) {
                echo "    - Ajout is_default\n";
                $db->statement("ALTER TABLE etat_document ADD COLUMN is_default TINYINT(1) DEFAULT 0");
            }
        }

        // 2. Harmonisation des tables métiers (ajout etat_id)
        $tablesToUpdate = ['bons_livraison', 'projets', 'inventaires'];
        foreach ($tablesToUpdate as $table) {
            if (Schema::connection('tenant')->hasTable($table)) {
                if (!Schema::connection('tenant')->hasColumn($table, 'etat_id')) {
                    echo "  - Ajout 'etat_id' à table '$table'...\n";
                    $db->statement("ALTER TABLE $table ADD COLUMN etat_id BIGINT UNSIGNED NULL AFTER statut");
                    $db->statement("ALTER TABLE $table ADD INDEX (etat_id)");
                }
            }
        }

        // 3. Peuplement du référentiel
        $defaultEtats = [
            // BL
            ['type_document' => 'bl', 'code' => 'BROUILLON', 'libelle' => 'Brouillon', 'color' => '#64748b', 'ordre' => 1, 'is_default' => 1],
            ['type_document' => 'bl', 'code' => 'VALIDE', 'libelle' => 'Validé', 'color' => '#10b981', 'ordre' => 2, 'is_default' => 0],
            ['type_document' => 'bl', 'code' => 'ANNULE', 'libelle' => 'Annulé', 'color' => '#ef4444', 'ordre' => 3, 'is_default' => 0],
            // Projets
            ['type_document' => 'projet', 'code' => 'NOUVEAU', 'libelle' => 'Nouveau', 'color' => '#3b82f6', 'ordre' => 1, 'is_default' => 1],
            ['type_document' => 'projet', 'code' => 'EN_COURS', 'libelle' => 'En cours', 'color' => '#f59e0b', 'ordre' => 2, 'is_default' => 0],
            ['type_document' => 'projet', 'code' => 'TERMINE', 'libelle' => 'Terminé', 'color' => '#10b981', 'ordre' => 3, 'is_default' => 0],
            ['type_document' => 'projet', 'code' => 'SUSPENDU', 'libelle' => 'Suspendu', 'color' => '#94a3b8', 'ordre' => 4, 'is_default' => 0],
            // Inventaires
            ['type_document' => 'inventaire', 'code' => 'BROUILLON', 'libelle' => 'Brouillon', 'color' => '#64748b', 'ordre' => 1, 'is_default' => 1],
            ['type_document' => 'inventaire', 'code' => 'VALIDE', 'libelle' => 'Validé', 'color' => '#10b981', 'ordre' => 2, 'is_default' => 0],
            ['type_document' => 'inventaire', 'code' => 'ANNULE', 'libelle' => 'Annulé', 'color' => '#ef4444', 'ordre' => 3, 'is_default' => 0],
        ];

        foreach ($defaultEtats as $et) {
            $exists = $db->table('etat_document')
                ->where('type_document', $et['type_document'])
                ->where('code', $et['code'])
                ->exists();
            
            if (!$exists) {
                echo "  - Ajout état '{$et['libelle']}' pour '{$et['type_document']}'...\n";
                // Suppression des colonnes non supportées par INSERT si nécessaire (on fait simple ici car on a harmonisé)
                $db->table('etat_document')->insert($et);
            }
        }

        echo "  [SUCCESS] Tenant harmonisé.\n";

    } catch (\Throwable $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n";
    }
}
