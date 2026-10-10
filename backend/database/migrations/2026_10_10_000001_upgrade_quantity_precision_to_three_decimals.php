<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'stocks'                     => ['quantite'],
            'mouvements_stock'           => ['quantite'],
            'mouvements_stocks'          => ['quantite'],
            'produits'                   => ['stock_actuel', 'stock_initial', 'seuil_alerte', 'stock_min', 'stock_max'],
            'ligne_facture'              => ['quantite'],
            'ligne_bon_livraison'        => ['quantite_prevue', 'quantite_livree'],
            'ligne_devis'                => ['quantite'],
            'ligne_bon_commande_client'  => ['quantite'],
            'ligne_avoir_client'         => ['quantite'],
            'nomenclature_produit'       => ['quantite'],
            'facture_achat_lignes'       => ['quantite'],
            'bcf_lignes'                 => ['quantite'],
            'br_lignes'                  => ['quantite_commandee', 'quantite_recue'],
            'avoir_achat_lignes'         => ['quantite'],
        ];

        foreach ($tables as $tbl => $cols) {
            if (Schema::connection('tenant')->hasTable($tbl)) {
                foreach ($cols as $col) {
                    if (Schema::connection('tenant')->hasColumn($tbl, $col)) {
                        DB::connection('tenant')->statement("ALTER TABLE `{$tbl}` MODIFY `{$col}` DECIMAL(24, 3) NOT NULL DEFAULT 0.000");
                    }
                }
            }
        }

        // Add unite to ligne_bon_livraison if missing
        if (Schema::connection('tenant')->hasTable('ligne_bon_livraison') && !Schema::connection('tenant')->hasColumn('ligne_bon_livraison', 'unite')) {
            Schema::connection('tenant')->table('ligne_bon_livraison', function (Blueprint $table) {
                $table->string('unite', 50)->nullable()->after('quantite_livree');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep 3 decimals
    }
};
