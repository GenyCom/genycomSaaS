<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = DB::select('SHOW COLUMNS FROM tenants');
foreach ($cols as $c) {
    echo "Field: {$c->Field}\n";
}
