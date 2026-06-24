<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;

echo "Fetching tenants for database updates (Cheques)...\n";
$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    $dbName = $tenant->database_name;
    echo ">>> Updating tenant database: $dbName\n";

    Config::set('database.connections.tenant.database', $dbName);
    Config::set('database.connections.tenant.username', $tenant->db_username ?: config('database.connections.central.username'));
    Config::set('database.connections.tenant.password', $tenant->db_password ?: config('database.connections.central.password'));
    DB::purge('tenant');
    DB::reconnect('tenant');

    try {
        Schema::connection('tenant')->table('reglements', function (Blueprint $table) {
            if (!Schema::connection('tenant')->hasColumn('reglements', 'date_echeance_cheque')) {
                $table->date('date_echeance_cheque')->nullable()->after('banque');
                echo "  -> Added date_echeance_cheque\n";
            } else {
                echo "  -> Column date_echeance_cheque already exists\n";
            }
            if (!Schema::connection('tenant')->hasColumn('reglements', 'statut_cheque')) {
                $table->string('statut_cheque', 30)->nullable()->default('en_attente')->after('date_echeance_cheque');
                echo "  -> Added statut_cheque\n";
            } else {
                echo "  -> Column statut_cheque already exists\n";
            }
            if (!Schema::connection('tenant')->hasColumn('reglements', 'image_cheque')) {
                $table->string('image_cheque', 500)->nullable()->after('statut_cheque');
                echo "  -> Added image_cheque\n";
            } else {
                echo "  -> Column image_cheque already exists\n";
            }
        });
    } catch (\Exception $e) {
        echo "Error updating database $dbName: " . $e->getMessage() . "\n";
    }
}

echo "All tenant updates (Cheques) completed!\n";
