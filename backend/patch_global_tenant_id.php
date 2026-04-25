<?php
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tablesToPatch = [
    // 003_tiers
    'clients', 'fournisseurs',
    // 004_produits
    'categories_produit', 'produits', 'famille_produit',
    // 005_ventes
    'projets', 'devis', 'ligne_devis', 'factures', 'ligne_facture',
    'bons_livraison', 'ligne_bon_livraison', 'avoirs_client', 'ligne_avoir_client',
    'bons_commande_client', 'ligne_bon_commande_client',
    // 006_achats
    'bons_commande_fournisseur', 'ligne_bon_commande_fournisseur', 'factures_fournisseur',
    'avoirs_fournisseur', 'ligne_avoir_fournisseur',
    // 007_stock_finances
    'entrepots', 'stocks', 'mouvements_stock', 'inventaires', 'reglements', 'depenses',
    'categories_depense', 'modes_paiement', 'virement_inter_comptes',
    // 008_rh_contrats (just in case)
    'contrats', 'ligne_contrats'
];

foreach (Tenant::all() as $tenant) {
    Config::set('database.connections.tenant.database', $tenant->database_name);
    DB::purge('tenant');
    echo "--- Patching {$tenant->database_name} ---\n";
    
    foreach ($tablesToPatch as $tableName) {
        try {
            if (!Schema::connection('tenant')->hasTable($tableName)) {
                echo "Table {$tableName} does not exist. Skipping.\n";
                continue;
            }

            if (!Schema::connection('tenant')->hasColumn($tableName, 'tenant_id')) {
                echo "Adding tenant_id to {$tableName}...\n";
                Schema::connection('tenant')->table($tableName, function ($table) {
                    $table->unsignedBigInteger('tenant_id')->after('id')->index();
                });
            } else {
                echo "Column tenant_id already exists in {$tableName}.\n";
            }
        } catch (\Exception $e) {
            echo "Error patching table {$tableName}: " . $e->getMessage() . "\n";
        }
    }
    echo "Done for {$tenant->database_name}\n\n";
}
