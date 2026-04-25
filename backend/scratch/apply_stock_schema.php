<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$tenants = DB::connection('central')->table('tenants')->get(['database_name']);

foreach ($tenants as $tenant) {
    echo "Updating tenant: {$tenant->database_name}...\n";
    try {
        Config::set('database.connections.tenant.database', $tenant->database_name);
        DB::purge('tenant');
        $conn = DB::connection('tenant');

        // Créer la table mouvements_stocks si absente
        if (!Schema::connection('tenant')->hasTable('mouvements_stocks')) {
            Schema::connection('tenant')->create('mouvements_stocks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('article_id');
                $table->enum('type_mouvement', ['ENTREE', 'SORTIE']);
                $table->decimal('quantite', 24, 2);
                $table->string('document_type');
                $table->unsignedBigInteger('document_id');
                $table->dateTime('date_mouvement');
                $table->unsignedBigInteger('utilisateur_id');
                $table->timestamps();
                $table->index(['article_id', 'tenant_id'], 'idx_ms_art_tenant');
            });
            echo "  - Table mouvements_stocks créée.\n";
        } else {
            echo "  - Table mouvements_stocks déjà présente.\n";
        }

        // Vérifier si la colonne stock_actuel existe dans produits
        if (Schema::connection('tenant')->hasTable('produits')) {
            if (!Schema::connection('tenant')->hasColumn('produits', 'stock_actuel')) {
                $conn->statement("ALTER TABLE `produits` ADD COLUMN `stock_actuel` DECIMAL(24,2) DEFAULT 0.00");
                echo "  - Colonne stock_actuel ajoutée à la table produits.\n";
            }
        }

    } catch (\Exception $e) {
        echo "  - Erreur: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
