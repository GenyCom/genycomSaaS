<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

Config::set('database.connections.tenant.database', 'genycom_sohan');
DB::purge('tenant');

$cols = DB::connection('tenant')->select('SHOW COLUMNS FROM bons_reception');
foreach ($cols as $c) {
    echo "Field: {$c->Field}\n";
}
