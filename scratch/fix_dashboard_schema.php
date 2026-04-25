<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Script de correction des erreurs de schéma et harmonisation des tenants.
 * Résout : 
 * 1. Propriété 'domain' au lieu de 'subdomain'.
 * 2. Colonne 'tenant_id' manquante dans 'alertes_stock'.
 */

require dirname(__DIR__) . '/backend/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Début de la correction du schéma...\n";

try {
    // 1. Récupération des tenants (Connection 'central')
    $tenants = DB::connection('central')->table('tenants')->get();

    foreach ($tenants as $tenant) {
        $name = $tenant->domain ?? $tenant->nom ?? "Tenant #{$tenant->id}";
        $dbName = $tenant->database_name;
        
        echo ">>> Traitement du tenant : {$name} (BDD: {$dbName})\n";

        if (!$dbName) {
            echo "    [!] Erreur : Pas de base de données définie pour ce tenant. Passage...\n";
            continue;
        }

        // Configuration dynamique de la connexion tenant
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant'); // Reboot de la connexion
        DB::reconnect('tenant');

        $schema = Schema::connection('tenant');
        $db = DB::connection('tenant');

        // Correction de alertes_stock
        if ($schema->hasTable('alertes_stock')) {
            if (!$schema->hasColumn('alertes_stock', 'tenant_id')) {
                echo "    [+] Ajout de la colonne tenant_id à alertes_stock...\n";
                $db->statement("ALTER TABLE alertes_stock ADD COLUMN tenant_id BIGINT UNSIGNED DEFAULT 0 AFTER id");
                $db->statement("CREATE INDEX idx_alertes_stock_tenant ON alertes_stock(tenant_id)");
                
                $db->table('alertes_stock')->update(['tenant_id' => $tenant->id]);
            } else {
                echo "    [i] Colonne tenant_id déjà présente dans alertes_stock.\n";
            }
        }

        // Harmonisation des tables d'achats
        $tablesToCheck = [
            'commandes' => 'bcf',
            'ligne_commande' => 'bcf_lignes',
            'bons_reception' => 'br',
            'reception_lignes' => 'br_lignes',
        ];

        foreach ($tablesToCheck as $old => $new) {
            if ($schema->hasTable($old) && !$schema->hasTable($new)) {
                echo "    [>] Renommage de {$old} en {$new}...\n";
                $schema->rename($old, $new);
            }
        }

        // Création de factures_achats si manquante
        if (!$schema->hasTable('factures_achats')) {
            echo "    [+] Création de la table factures_achats...\n";
            $schema->create('factures_achats', function ($table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->string('numero')->unique();
                $table->unsignedBigInteger('fournisseur_id');
                $table->date('date_facture');
                $table->date('date_echeance')->nullable();
                $table->decimal('montant_ht', 15, 2)->default(0);
                $table->decimal('montant_tva', 15, 2)->default(0);
                $table->decimal('montant_ttc', 15, 2)->default(0);
                $table->decimal('montant_regle', 15, 2)->default(0);
                $table->decimal('reste_a_payer', 15, 2)->default(0);
                $table->string('statut')->default('brouillon');
                $table->text('observations')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!$schema->hasTable('facture_achat_lignes')) {
            echo "    [+] Création de la table facture_achat_lignes...\n";
            $schema->create('facture_achat_lignes', function ($table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->unsignedBigInteger('facture_achat_id')->index();
                $table->unsignedBigInteger('produit_id')->nullable();
                $table->string('designation');
                $table->decimal('quantite', 15, 3);
                $table->decimal('prix_unitaire', 15, 2);
                $table->decimal('taux_tva', 5, 2)->default(20);
                $table->decimal('montant_ht', 15, 2);
                $table->decimal('montant_tva', 15, 2);
                $table->decimal('montant_ttc', 15, 2);
                $table->integer('ordre')->default(0);
            });
        }
    }

    echo "\nTerminé ! Le schéma a été corrigé et harmonisé pour tous les tenants.\n";

} catch (\Exception $e) {
    echo "\nERREUR FATALE : " . $e->getMessage() . "\n";
    echo "Ligne : " . $e->getLine() . "\n";
}
