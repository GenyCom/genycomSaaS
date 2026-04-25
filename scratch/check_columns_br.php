<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');
$db = DB::connection('tenant');

echo "--- COLUMNS reception_lignes ---\n";
print_r(Schema::connection('tenant')->getColumnListing('reception_lignes'));

echo "\n--- COLUMNS ligne_bon_reception ---\n";
print_r(Schema::connection('tenant')->getColumnListing('ligne_bon_reception'));
