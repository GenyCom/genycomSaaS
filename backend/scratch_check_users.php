<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$users = DB::connection('central')->table('tenant_user')->where('tenant_id', 1)->get();
echo "Users for Tenant 1:\n";
foreach ($users as $u) {
    echo "User ID: {$u->user_id}, Role: {$u->role_id}\n";
}
