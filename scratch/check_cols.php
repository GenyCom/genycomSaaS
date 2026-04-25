<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenant = DB::table('tenants')->first();
config(['database.connections.tenant.database' => $tenant->database_name]);
DB::purge('tenant');

foreach(['contrats','depenses','dettes_fournisseur'] as $t) {
    if (Schema::connection('tenant')->hasTable($t)) {
        echo "Table: $t\n";
        print_r(Schema::connection('tenant')->getColumnListing($t));
    } else {
        echo "Table: $t (Manquante)\n";
    }
}
