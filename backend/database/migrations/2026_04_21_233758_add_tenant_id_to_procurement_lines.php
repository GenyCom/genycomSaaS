<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // On ajoute tenant_id aux tables de lignes du cycle achat qui ont été créées sans
        
        if (Schema::hasTable('bcf_lignes') && !Schema::hasColumn('bcf_lignes', 'tenant_id')) {
            Schema::table('bcf_lignes', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->after('id')->default(1);
            });
        }

        if (Schema::hasTable('br_lignes') && !Schema::hasColumn('br_lignes', 'tenant_id')) {
            Schema::table('br_lignes', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->after('id')->default(1);
            });
        }

        if (Schema::hasTable('facture_achat_lignes') && !Schema::hasColumn('facture_achat_lignes', 'tenant_id')) {
            Schema::table('facture_achat_lignes', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->after('id')->default(1);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bcf_lignes', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
        Schema::table('br_lignes', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
        Schema::table('facture_achat_lignes', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }
};
