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

        // --- 1. Harmonisation BCF (Commandes Fournisseur) ---
        if (Schema::connection('tenant')->hasTable('commandes') && !Schema::connection('tenant')->hasTable('bcf')) {
            echo "  Renaming 'commandes' to 'bcf'...\n";
            Schema::connection('tenant')->rename('commandes', 'bcf');
        }

        if (Schema::connection('tenant')->hasTable('bcf')) {
            Schema::connection('tenant')->table('bcf', function (Blueprint $table) {
                if (!Schema::connection('tenant')->hasColumn('bcf', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->default(1)->after('id');
                }
                if (!Schema::connection('tenant')->hasColumn('bcf', 'statut')) {
                    $table->enum('statut', ['brouillon', 'valide', 'reception_partielle', 'clos', 'annule'])->default('valide')->after('total_ttc');
                }
                if (!Schema::connection('tenant')->hasColumn('bcf', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
            // Update tenant_id
            $conn->table('bcf')->update(['tenant_id' => $tenant->id]);
        }

        // --- 2. Harmonisation BR (Bons de Réception) ---
        if (Schema::connection('tenant')->hasTable('bons_reception') && !Schema::connection('tenant')->hasTable('br')) {
            echo "  Renaming 'bons_reception' to 'br'...\n";
            Schema::connection('tenant')->rename('bons_reception', 'br');
        }

        if (Schema::connection('tenant')->hasTable('br')) {
            Schema::connection('tenant')->table('br', function (Blueprint $table) {
                if (!Schema::connection('tenant')->hasColumn('br', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->default(1)->after('id');
                }
                if (Schema::connection('tenant')->hasColumn('br', 'commande_id') && !Schema::connection('tenant')->hasColumn('br', 'bcf_id')) {
                    $table->renameColumn('commande_id', 'bcf_id');
                }
                if (!Schema::connection('tenant')->hasColumn('br', 'statut')) {
                    $table->enum('statut', ['brouillon', 'valide', 'annule'])->default('valide')->after('fournisseur_id');
                }
            });
            $conn->table('br')->update(['tenant_id' => $tenant->id]);
        }

        // --- 3. Harmonisation Lignes BR ---
        if (Schema::connection('tenant')->hasTable('ligne_bon_reception') && !Schema::connection('tenant')->hasTable('br_lignes')) {
            echo "  Renaming 'ligne_bon_reception' to 'br_lignes'...\n";
            Schema::connection('tenant')->rename('ligne_bon_reception', 'br_lignes');
        }

        if (Schema::connection('tenant')->hasTable('br_lignes')) {
            Schema::connection('tenant')->table('br_lignes', function (Blueprint $table) {
                if (Schema::connection('tenant')->hasColumn('br_lignes', 'bon_reception_id') && !Schema::connection('tenant')->hasColumn('br_lignes', 'br_id')) {
                    $table->renameColumn('bon_reception_id', 'br_id');
                }
                if (!Schema::connection('tenant')->hasColumn('br_lignes', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->default(1)->after('id');
                }
                if (!Schema::connection('tenant')->hasColumn('br_lignes', 'quantite_commandee')) {
                    $table->decimal('quantite_commandee', 24, 2)->default(0)->after('designation');
                }
                if (Schema::connection('tenant')->hasColumn('br_lignes', 'quantite') && !Schema::connection('tenant')->hasColumn('br_lignes', 'quantite_recue')) {
                    $table->renameColumn('quantite', 'quantite_recue');
                }
            });
            $conn->table('br_lignes')->update(['tenant_id' => $tenant->id]);
        }

        // --- 4. Harmonisation Factures Achat ---
        if (Schema::connection('tenant')->hasTable('facture_achat') && !Schema::connection('tenant')->hasTable('factures_achats')) {
            echo "  Renaming 'facture_achat' to 'factures_achats'...\n";
            Schema::connection('tenant')->rename('facture_achat', 'factures_achats');
        }

        if (Schema::connection('tenant')->hasTable('factures_achats')) {
            Schema::connection('tenant')->table('factures_achats', function (Blueprint $table) {
                if (!Schema::connection('tenant')->hasColumn('factures_achats', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->default(1)->after('id');
                }
                if (!Schema::connection('tenant')->hasColumn('factures_achats', 'statut')) {
                    $table->enum('statut', ['brouillon', 'valide', 'paye', 'partiellement_paye', 'annule'])->default('brouillon')->after('reste_a_payer');
                }
            });
            $conn->table('factures_achats')->update(['tenant_id' => $tenant->id]);
        }

        echo "  Tenant {$tenant->domain} standardized.\n";

    } catch (\Exception $e) {
        echo "  Error standardizing tenant {$tenant->domain}: " . $e->getMessage() . "\n";
    }
}

echo "\nHarmonization complete.\n";
