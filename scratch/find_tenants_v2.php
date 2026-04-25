<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tenants = DB::table('tenants')->get();
foreach ($tenants as $t) {
    echo "ID: {$t->id} | Domain: {$t->domain} | DB: {$t->database_name}\n";
}
