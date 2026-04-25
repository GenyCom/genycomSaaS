<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dettes_fournisseur', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('numero')->unique();
            $table->unsignedBigInteger('fournisseur_id')->index();
            $table->unsignedBigInteger('facture_id')->nullable()->index();
            $table->unsignedBigInteger('bon_reception_id')->nullable()->index();
            
            $table->date('date_dette');
            $table->date('date_echeance')->nullable();
            
            // Gestion des montants avec précision financière
            $table->decimal('montant_ht', 15, 2)->default(0);
            $table->decimal('montant_tva', 15, 2)->default(0);
            $table->decimal('montant_total', 15, 2)->default(0);
            $table->decimal('montant_restant', 15, 2)->default(0);
            $table->decimal('montant_regle', 15, 2)->default(0);
            
            $table->timestamps();
            $table->softDeletes(); // Car ton DetteController utilise "deleted_at IS NULL"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dettes_fournisseur');
    }
};