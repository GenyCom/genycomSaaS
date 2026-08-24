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
        if (config('database.connections.tenant.database')) {
            Schema::connection('tenant')->table('clients', function (Blueprint $table) {
                if (!Schema::connection('tenant')->hasColumn('clients', 'solde_initial')) {
                    $table->decimal('solde_initial', 15, 2)->default(0)->after('delai_paiement')->comment('Solde dû à l\'ouverture du compte (migration)');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.connections.tenant.database')) {
            Schema::connection('tenant')->table('clients', function (Blueprint $table) {
                if (Schema::connection('tenant')->hasColumn('clients', 'solde_initial')) {
                    $table->dropColumn('solde_initial');
                }
            });
        }
    }
};
