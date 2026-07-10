<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;

echo "Fetching tenants for database updates...\n";
try {
    $tenants = DB::connection('central')->table('tenants')->get();
} catch (\Exception $e) {
    echo "Error querying central database: " . $e->getMessage() . "\n";
    exit(1);
}

if ($tenants->isEmpty()) {
    echo "No tenants found.\n";
    exit;
}

foreach ($tenants as $tenant) {
    $dbName = $tenant->database_name;
    echo ">>> Updating tenant database: $dbName\n";

    // Configure the connection dynamically
    Config::set('database.connections.tenant.database', $dbName);
    DB::purge('tenant');
    DB::reconnect('tenant');

    try {
        Schema::connection('tenant')->table('clients', function (Blueprint $table) use ($dbName) {
            echo "Checking clients table in $dbName...\n";
            if (!Schema::connection('tenant')->hasColumn('clients', 'taux_remise')) {
                $table->decimal('taux_remise', 5, 2)->default(0.00)->nullable()->after('solde_initial');
                echo "Added taux_remise to clients\n";
            }
            if (Schema::connection('tenant')->hasColumn('clients', 'marge_minimale')) {
                $table->dropColumn('marge_minimale');
                echo "Dropped marge_minimale from clients\n";
            }
            if (Schema::connection('tenant')->hasColumn('clients', 'marge_maximale')) {
                $table->dropColumn('marge_maximale');
                echo "Dropped marge_maximale from clients\n";
            }
            if (!Schema::connection('tenant')->hasColumn('clients', 'is_default')) {
                $table->boolean('is_default')->default(false)->nullable()->after('is_active');
                echo "Added is_default to clients\n";
            }
        });

        Schema::connection('tenant')->table('ligne_devis', function (Blueprint $table) use ($dbName) {
            if (Schema::connection('tenant')->hasColumn('ligne_devis', 'prix_achat')) {
                $table->dropColumn('prix_achat');
                echo "Dropped prix_achat from ligne_devis\n";
            }
        });

        Schema::connection('tenant')->table('ligne_facture', function (Blueprint $table) use ($dbName) {
            if (Schema::connection('tenant')->hasColumn('ligne_facture', 'prix_achat')) {
                $table->dropColumn('prix_achat');
                echo "Dropped prix_achat from ligne_facture\n";
            }
        });

        Schema::connection('tenant')->table('ligne_bon_commande_client', function (Blueprint $table) use ($dbName) {
            if (Schema::connection('tenant')->hasColumn('ligne_bon_commande_client', 'prix_achat')) {
                $table->dropColumn('prix_achat');
                echo "Dropped prix_achat from ligne_bon_commande_client\n";
            }
        });

        Schema::connection('tenant')->table('ligne_bon_livraison', function (Blueprint $table) use ($dbName) {
            if (Schema::connection('tenant')->hasColumn('ligne_bon_livraison', 'prix_achat')) {
                $table->dropColumn('prix_achat');
                echo "Dropped prix_achat from ligne_bon_livraison\n";
            }
        });

    } catch (\Exception $e) {
        echo "Error updating database $dbName: " . $e->getMessage() . "\n";
    }
}

echo "All tenant updates completed!\n";
