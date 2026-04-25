<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tenants = \App\Models\Tenant::all();
foreach ($tenants as $t) {
    echo "ID: " . $t->id . " | NOM: " . $t->nom . " | DB: " . $t->database_name . "\n";
}
