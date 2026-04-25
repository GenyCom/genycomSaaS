<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Harmonisation Statuts Tenant: {$tenant->nom} ({$tenant->database_name})\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    $db = DB::connection('tenant');

    try {
        // 1. Mise à jour du schéma
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

        // 2. Peuplement du référentiel etat_document
        $defaultEtats = [
            // BL
            ['type_document' => 'bl', 'code' => 'BROUILLON', 'libelle' => 'Brouillon', 'couleur_bg' => '#f1f5f9', 'couleur_texte' => '#475569', 'ordre' => 1],
            ['type_document' => 'bl', 'code' => 'VALIDE', 'libelle' => 'Validé', 'couleur_bg' => '#dcfce7', 'couleur_texte' => '#15803d', 'ordre' => 2],
            ['type_document' => 'bl', 'code' => 'ANNULE', 'libelle' => 'Annulé', 'couleur_bg' => '#fee2e2', 'couleur_texte' => '#b91c1c', 'ordre' => 3],
            // Projets
            ['type_document' => 'projet', 'code' => 'NOUVEAU', 'libelle' => 'Nouveau', 'couleur_bg' => '#eff6ff', 'couleur_texte' => '#1d4ed8', 'ordre' => 1],
            ['type_document' => 'projet', 'code' => 'EN_COURS', 'libelle' => 'En cours', 'couleur_bg' => '#fffbeb', 'couleur_texte' => '#b45309', 'ordre' => 2],
            ['type_document' => 'projet', 'code' => 'TERMINE', 'libelle' => 'Terminé', 'couleur_bg' => '#f0fdf4', 'couleur_texte' => '#15803d', 'ordre' => 3],
            ['type_document' => 'projet', 'code' => 'SUSPENDU', 'libelle' => 'Suspendu', 'couleur_bg' => '#f8fafc', 'couleur_texte' => '#64748b', 'ordre' => 4],
            // Inventaires
            ['type_document' => 'inventaire', 'code' => 'BROUILLON', 'libelle' => 'Brouillon', 'couleur_bg' => '#f1f5f9', 'couleur_texte' => '#475569', 'ordre' => 1],
            ['type_document' => 'inventaire', 'code' => 'VALIDE', 'libelle' => 'Validé', 'couleur_bg' => '#dcfce7', 'couleur_texte' => '#15803d', 'ordre' => 2],
            ['type_document' => 'inventaire', 'code' => 'ANNULE', 'libelle' => 'Annulé', 'couleur_bg' => '#fee2e2', 'couleur_texte' => '#b91c1c', 'ordre' => 3],
        ];

        foreach ($defaultEtats as $et) {
            $exists = $db->table('etat_document')
                ->where('type_document', $et['type_document'])
                ->where('code', $et['code'])
                ->exists();
            
            if (!$exists) {
                echo "  - Ajout état '{$et['libelle']}' pour '{$et['type_document']}'...\n";
                $db->table('etat_document')->insert($et);
            }
        }

        echo "  [SUCCESS] Tenant harmonisé.\n";

    } catch (\Throwable $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n";
    }
}
