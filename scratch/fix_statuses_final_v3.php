<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Réparation Harmonisation Tenant: {$tenant->nom} ({$tenant->database_name})\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    $db = DB::connection('tenant');

    try {
        // 1. Audit structurel manuel
        $cols = collect($db->select("SHOW COLUMNS FROM etat_document"))->pluck('Field')->toArray();
        echo "  - Colonnes actuelles: " . implode(', ', $cols) . "\n";

        if (in_array('couleur_bg', $cols) && !in_array('color', $cols)) {
            echo "    -> Renommage couleur_bg vers color\n";
            $db->statement("ALTER TABLE etat_document CHANGE couleur_bg color VARCHAR(50)");
            $cols[] = 'color';
        }
        if (!in_array('badge_class', $cols)) {
            echo "    -> Ajout badge_class\n";
            $db->statement("ALTER TABLE etat_document ADD COLUMN badge_class VARCHAR(100) NULL");
        }
        if (!in_array('is_default', $cols)) {
            echo "    -> Ajout is_default\n";
            $db->statement("ALTER TABLE etat_document ADD COLUMN is_default TINYINT(1) DEFAULT 0");
        }

        // 2. Harmonisation des tables métiers
        foreach (['bons_livraison', 'projets', 'inventaires'] as $table) {
            $tableCols = collect($db->select("SHOW COLUMNS FROM $table"))->pluck('Field')->toArray();
            if (!in_array('etat_id', $tableCols)) {
                echo "    -> Ajout etat_id à $table\n";
                $db->statement("ALTER TABLE $table ADD COLUMN etat_id BIGINT UNSIGNED NULL AFTER statut");
            }
        }

        // 3. Peuplement
        $defaultEtats = [
            ['type_document' => 'bl', 'code' => 'BROUILLON', 'libelle' => 'Brouillon', 'color' => '#64748b', 'ordre' => 1, 'is_default' => 1],
            ['type_document' => 'bl', 'code' => 'VALIDE', 'libelle' => 'Validé', 'color' => '#10b981', 'ordre' => 2, 'is_default' => 0],
            ['type_document' => 'projet', 'code' => 'NOUVEAU', 'libelle' => 'Nouveau', 'color' => '#3b82f6', 'ordre' => 1, 'is_default' => 1],
            ['type_document' => 'projet', 'code' => 'EN_COURS', 'libelle' => 'En cours', 'color' => '#f59e0b', 'ordre' => 2, 'is_default' => 0],
            ['type_document' => 'inventaire', 'code' => 'BROUILLON', 'libelle' => 'Brouillon', 'color' => '#64748b', 'ordre' => 1, 'is_default' => 1],
        ];

        foreach ($defaultEtats as $et) {
            $exists = $db->table('etat_document')
                ->where('type_document', $et['type_document'])
                ->where('code', $et['code'])
                ->exists();
            if (!$exists) {
                echo "    -> Ajout état {$et['libelle']} pour {$et['type_document']}\n";
                $db->table('etat_document')->insert($et);
            }
        }

        echo "  [SUCCESS] Tenant réparé.\n";

    } catch (\Throwable $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n";
    }
}
