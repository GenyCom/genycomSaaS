<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;

echo "Fetching tenants for database updates (Products)...\n";
$tenants = DB::connection('central')->table('tenants')->get();

if ($tenants->isEmpty()) {
    echo "No tenants found.\n";
    exit;
}

foreach ($tenants as $tenant) {
    $dbName = $tenant->database_name;
    echo ">>> Updating tenant database: $dbName\n";

    Config::set('database.connections.tenant.database', $dbName);
    DB::purge('tenant');
    DB::reconnect('tenant');

    try {
        Schema::connection('tenant')->table('produits', function (Blueprint $table) use ($dbName) {
            echo "Checking produits table in $dbName...\n";
            
            if (!Schema::connection('tenant')->hasColumn('produits', 'marque')) {
                $table->string('marque', 100)->nullable()->after('code_barre');
                echo "Added marque\n";
            }
            if (!Schema::connection('tenant')->hasColumn('produits', 'marge_pourcentage')) {
                $table->decimal('marge_pourcentage', 5, 2)->default(0.00)->after('prix_ht_vente');
                echo "Added marge_pourcentage\n";
            }
            if (!Schema::connection('tenant')->hasColumn('produits', 'is_perissable')) {
                $table->boolean('is_perissable')->default(false)->after('prix_ttc_vente');
                echo "Added is_perissable\n";
            }
            if (!Schema::connection('tenant')->hasColumn('produits', 'is_lot')) {
                $table->boolean('is_lot')->default(false)->after('is_perissable');
                echo "Added is_lot\n";
            }
            if (!Schema::connection('tenant')->hasColumn('produits', 'garantie_mois')) {
                $table->integer('garantie_mois')->default(0)->after('is_lot');
                echo "Added garantie_mois\n";
            }
        });

    } catch (\Exception $e) {
        echo "Error updating database $dbName: " . $e->getMessage() . "\n";
    }
}

echo "All tenant updates (Products) completed!\n";
