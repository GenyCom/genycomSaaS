<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.connections.tenant.database')) {
            if (!Schema::connection('tenant')->hasTable('pos_clotures')) {
                Schema::connection('tenant')->create('pos_clotures', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('tenant_id')->default(1)->index();
                    $table->string('numero', 50);
                    $table->date('date_cloture')->index();
                    $table->dateTime('opened_at');
                    $table->dateTime('closed_at');
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->string('nom_caissier', 150)->nullable();
                    $table->decimal('fond_initial', 15, 2)->default(0.00);
                    $table->decimal('total_ventes', 15, 2)->default(0.00);
                    $table->decimal('total_especes', 15, 2)->default(0.00);
                    $table->decimal('total_carte', 15, 2)->default(0.00);
                    $table->decimal('total_cheque', 15, 2)->default(0.00);
                    $table->decimal('total_virement', 15, 2)->default(0.00);
                    $table->decimal('total_autre', 15, 2)->default(0.00);
                    $table->decimal('total_attendu', 15, 2)->default(0.00);
                    $table->decimal('total_declare', 15, 2)->default(0.00);
                    $table->decimal('ecart', 15, 2)->default(0.00);
                    $table->string('statut_ecart', 30)->default('conforme'); // conforme, manquant, excedent
                    $table->unsignedInteger('nb_ventes')->default(0);
                    $table->text('observations')->nullable();
                    $table->mediumText('rapport_texte')->nullable();
                    $table->timestamps();
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        if (config('database.connections.tenant.database')) {
            Schema::connection('tenant')->dropIfExists('pos_clotures');
        }
    }
};
