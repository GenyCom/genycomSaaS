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
        // Table de traçabilité des stocks (Le livre de bord)
        Schema::create('mouvements_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable(); 
            $table->unsignedBigInteger('article_id');
            $table->enum('type_mouvement', ['ENTREE', 'SORTIE']);
            $table->decimal('quantite', 24, 2);
            $table->string('document_type'); // ex: 'BR', 'BL', 'AVOIR_CLIENT'
            $table->unsignedBigInteger('document_id');
            $table->dateTime('date_mouvement');
            $table->unsignedBigInteger('utilisateur_id');
            $table->timestamps();

            $table->index(['article_id', 'tenant_id']);
        });

        // Comme la table existante s'appelle 'produits' avec une colonne 'stock_actuel', 
        // je m'assure de la compatibilité avec la demande utilisateur (articles / quantite_en_stock)
        // en ajoutant des alias ou en renommant si nécessaire. Pour rester prudent, 
        // je vais simplement utiliser les noms existants dans le service SQL.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvements_stocks');
    }
};
