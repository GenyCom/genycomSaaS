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
        if (!Schema::connection('tenant')->hasColumn('commandes', 'etat_id')) {
            echo "    - Ajout de la colonne 'etat_id' à la table 'commandes'...\n";
            $db->statement("ALTER TABLE commandes ADD COLUMN etat_id BIGINT UNSIGNED NULL AFTER total_ttc");
            $db->statement("ALTER TABLE commandes ADD INDEX (etat_id)");
        } else {
            echo "    [OK] Colonne 'etat_id' déjà présente.\n";
        }

        // 2. Créer les états de base pour bcf
        $states = [
            ['code' => 'BROUILLON',     'libelle' => 'Brouillon',           'couleur' => '#94a3b8', 'ordre' => 10],
            ['code' => 'ENVOYE',        'libelle' => 'Envoyé',              'couleur' => '#3b82f6', 'ordre' => 20],
            ['code' => 'PARTIEL',      'libelle' => 'Réception Partielle',  'couleur' => '#f59e0b', 'ordre' => 30],
            ['code' => 'RECU',          'libelle' => 'Réceptionnée',         'couleur' => '#10b981', 'ordre' => 40],
            ['code' => 'ANNULE',        'libelle' => 'Annulée',              'couleur' => '#ef4444', 'ordre' => 99],
        ];

        foreach ($states as $s) {
            $exists = $db->table('etat_document')
                ->where('type_document', 'bcf')
                ->where('code', $s['code'])
                ->first();

            if (!$exists) {
                echo "    - Création de l'état {$s['code']}...\n";
                $db->table('etat_document')->insert(array_merge($s, [
                    'tenant_id' => $tenant->id,
                    'type_document' => 'bcf',
                    'is_system' => 1,
                    'created_at' => now(),
                ]));
            }
        }

        // 3. Initialiser etat_id pour les commandes existantes
        $defaut = $db->table('etat_document')->where('type_document', 'bcf')->where('code', 'BROUILLON')->first();
        $valide = $db->table('etat_document')->where('type_document', 'bcf')->where('code', 'RECU')->first();

        if ($defaut) {
            $db->table('commandes')->whereNull('etat_id')->update(['etat_id' => $defaut->id]);
            
            // Si est_livree = 1, on met l'état RECU
            if ($valide) {
                $db->table('commandes')->where('est_livree', 1)->update(['etat_id' => $valide->id]);
            }
        }

        echo "    [SUCCESS] Tenant {$tenant->nom} à jour.\n";

    } catch (\Throwable $e) {
        echo "    [ERROR] " . $e->getMessage() . "\n";
    }
}
