<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::first();
echo "COLUMNS: " . implode(', ', array_keys($user->getAttributes())) . "\n";
