<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

Config::set('database.connections.tenant.database', 'genycom_auto_sohan');
DB::purge('tenant');

$tables = ['bons_reception', 'ligne_bon_reception', 'bons_commande_fournisseur', 'dettes_fournisseur', 'stocks'];
foreach ($tables as $t) {
    echo "\n=== $t ===\n";
    $cols = DB::connection('tenant')->select("SHOW COLUMNS FROM $t");
    foreach ($cols as $c) echo "  " . $c->Field . "\n";
}
