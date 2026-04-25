<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;

echo "Fetching tenants for database updates (Projects)...\n";
$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    $dbName = $tenant->database_name;
    echo ">>> Updating tenant database: $dbName\n";

    Config::set('database.connections.tenant.database', $dbName);
    DB::purge('tenant');
    DB::reconnect('tenant');

    try {
        Schema::connection('tenant')->table('projets', function (Blueprint $table) {
            if (!Schema::connection('tenant')->hasColumn('projets', 'type_projet')) {
                $table->string('type_projet', 100)->nullable()->after('client_id');
                echo "Added type_projet\n";
            }
            if (!Schema::connection('tenant')->hasColumn('projets', 'avancement_pcent')) {
                $table->integer('avancement_pcent')->default(0)->after('statut');
                echo "Added avancement_pcent\n";
            }
        });
    } catch (\Exception $e) {
        echo "Error updating database $dbName: " . $e->getMessage() . "\n";
    }
}

echo "All tenant updates (Projects) completed!\n";
