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
    DB::reconnect('tenant');

    try {
        $db = DB::connection('tenant');

        // 1. Ajouter la colonne etat_id si manquante
        if (!Schema::connection('tenant')->hasColumn('devis', 'etat_id')) {
            echo "    - Ajout de la colonne 'etat_id' à la table 'devis'...\n";
            $db->statement("ALTER TABLE devis ADD COLUMN etat_id BIGINT UNSIGNED NULL AFTER total_remise");
            $db->statement("ALTER TABLE devis ADD INDEX (etat_id)");
        } else {
            echo "    [OK] Colonne 'etat_id' déjà présente.\n";
        }

        // 2. Créer les états de base pour devis
        $states = [
            ['code' => 'BROUILLON',  'libelle' => 'Brouillon', 'couleur' => '#94a3b8', 'ordre' => 10],
            ['code' => 'ENVOYE',     'libelle' => 'Envoyé',    'couleur' => '#3b82f6', 'ordre' => 20],
            ['code' => 'ACCEPTE',    'libelle' => 'Accepté',   'couleur' => '#10b981', 'ordre' => 30],
            ['code' => 'REFUSE',     'libelle' => 'Refusé',    'couleur' => '#ef4444', 'ordre' => 40],
            ['code' => 'EXPIRE',     'libelle' => 'Expiré',    'couleur' => '#6b7280', 'ordre' => 50],
        ];

        foreach ($states as $s) {
            $exists = $db->table('etat_document')
                ->where('type_document', 'devis')
                ->where('code', $s['code'])
                ->first();

            if (!$exists) {
                echo "    - Création de l'état {$s['code']}...\n";
                $db->table('etat_document')->insert(array_merge($s, [
                    'tenant_id' => $tenant->id,
                    'type_document' => 'devis',
                    'is_system' => 1,
                    'created_at' => now(),
                ]));
            }
        }

        // 3. Initialiser etat_id pour les devis existants
        $defaut = $db->table('etat_document')->where('type_document', 'devis')->where('code', 'BROUILLON')->first();
        if ($defaut) {
            $db->table('devis')->whereNull('etat_id')->update(['etat_id' => $defaut->id]);
        }

        echo "    [SUCCESS] Tenant {$tenant->nom} à jour.\n";

    } catch (\Throwable $e) {
        echo "    [ERROR] " . $e->getMessage() . "\n";
    }
}
