<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$roles = DB::connection('central')->table('roles')->get();
echo "ROLES LIST:\n";
print_r($roles->toArray());

$cols = DB::connection('central')->select("SHOW COLUMNS FROM roles");
echo "\nCOLUMNS LIST:\n";
print_r($cols);
