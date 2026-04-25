<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenants = DB::table('tenants')->get();

foreach ($tenants as $tenant) {
    echo "Processing tenant: {$tenant->domain} ({$tenant->database_name})\n";
    
    // Switch connection
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    
    try {
        $conn = DB::connection('tenant');

        // --- 1. BCF Lines ---
        if (Schema::connection('tenant')->hasTable('ligne_commande') && !Schema::connection('tenant')->hasTable('bcf_lignes')) {
            echo "  Renaming 'ligne_commande' to 'bcf_lignes'...\n";
            Schema::connection('tenant')->rename('ligne_commande', 'bcf_lignes');
        }

        if (Schema::connection('tenant')->hasTable('bcf_lignes')) {
            Schema::connection('tenant')->table('bcf_lignes', function (Blueprint $table) {
                if (Schema::connection('tenant')->hasColumn('bcf_lignes', 'commande_id') && !Schema::connection('tenant')->hasColumn('bcf_lignes', 'bcf_id')) {
                    $table->renameColumn('commande_id', 'bcf_id');
                }
                if (!Schema::connection('tenant')->hasColumn('bcf_lignes', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->default(1)->after('id');
                }
            });
            $conn->table('bcf_lignes')->update(['tenant_id' => $tenant->id]);
        }

        // --- 2. Factures Achats (Ensure existence) ---
        if (!Schema::connection('tenant')->hasTable('factures_achats')) {
            echo "  Creating 'factures_achats' table...\n";
            Schema::connection('tenant')->create('factures_achats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->default(1);
                $table->string('numero', 150)->unique();
                $table->unsignedBigInteger('fournisseur_id');
                $table->date('date_facture');
                $table->date('date_echeance')->nullable();
                $table->decimal('montant_ht', 24, 2)->default(0);
                $table->decimal('montant_tva', 24, 2)->default(0);
                $table->decimal('montant_ttc', 24, 2)->default(0);
                $table->decimal('montant_paye', 24, 2)->default(0);
                $table->decimal('reste_a_payer', 24, 2)->default(0);
                $table->enum('statut', ['brouillon', 'valide', 'paye', 'partiellement_paye', 'annule'])->default('brouillon');
                $table->longText('observations')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::connection('tenant')->hasTable('facture_achat_lignes')) {
            echo "  Creating 'facture_achat_lignes' table...\n";
            Schema::connection('tenant')->create('facture_achat_lignes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->default(1);
                $table->unsignedBigInteger('facture_achat_id');
                $table->unsignedBigInteger('produit_id')->nullable();
                $table->text('designation');
                $table->decimal('quantite', 24, 2)->default(1);
                $table->decimal('prix_unitaire', 24, 4)->default(0);
                $table->decimal('taux_tva', 5, 2)->default(20);
                $table->decimal('montant_ht', 24, 2)->default(0);
                $table->decimal('montant_tva', 24, 2)->default(0);
                $table->decimal('montant_ttc', 24, 2)->default(0);
                $table->smallInteger('ordre')->default(0);
            });
        }

        // --- 3. Fix BR column 'commande_id' if still exists ---
        if (Schema::connection('tenant')->hasTable('br')) {
            Schema::connection('tenant')->table('br', function (Blueprint $table) {
                if (Schema::connection('tenant')->hasColumn('br', 'commande_id') && !Schema::connection('tenant')->hasColumn('br', 'bcf_id')) {
                    $table->renameColumn('commande_id', 'bcf_id');
                }
            });
        }

        echo "  Tenant {$tenant->domain} fixed.\n";

    } catch (\Exception $e) {
        echo "  Error on tenant {$tenant->domain}: " . $e->getMessage() . "\n";
    }
}

echo "\nHarmonization Part 2 complete.\n";
