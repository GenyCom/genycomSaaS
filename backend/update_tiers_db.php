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
$tenants = DB::connection('central')->table('tenants')->get();

if ($tenants->isEmpty()) {
    echo "No tenants found.\n";
    exit;
}

foreach ($tenants as $tenant) {
    $dbName = $tenant->database_name;
    echo ">>> Updating tenant database: $dbName\n";

    // Configure the tenant connection dynamically
    Config::set('database.connections.tenant.database', $dbName);
    DB::purge('tenant');
    DB::reconnect('tenant');

    try {
        Schema::connection('tenant')->table('clients', function (Blueprint $table) use ($dbName) {
            echo "Checking clients table in $dbName...\n";
            if (!Schema::connection('tenant')->hasColumn('clients', 'if_fiscal')) {
                $table->string('if_fiscal', 50)->nullable()->after('rc');
                echo "Added if_fiscal to clients\n";
            }
            if (!Schema::connection('tenant')->hasColumn('clients', 'patente')) {
                $table->string('patente', 50)->nullable()->after('if_fiscal');
                echo "Added patente to clients\n";
            }
            if (!Schema::connection('tenant')->hasColumn('clients', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('updated_at');
                echo "Added is_active to clients\n";
            }
        });

        Schema::connection('tenant')->table('fournisseurs', function (Blueprint $table) use ($dbName) {
            echo "Checking fournisseurs table in $dbName...\n";
            if (!Schema::connection('tenant')->hasColumn('fournisseurs', 'if_fiscal')) {
                $table->string('if_fiscal', 50)->nullable()->after('rc');
                echo "Added if_fiscal to fournisseurs\n";
            }
            if (!Schema::connection('tenant')->hasColumn('fournisseurs', 'patente')) {
                $table->string('patente', 50)->nullable()->after('if_fiscal');
                echo "Added patente to fournisseurs\n";
            }
            if (!Schema::connection('tenant')->hasColumn('fournisseurs', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('updated_at');
                echo "Added is_active to fournisseurs\n";
            }
        });

    } catch (\Exception $e) {
        echo "Error updating database $dbName: " . $e->getMessage() . "\n";
    }
}

echo "All tenant updates completed!\n";
