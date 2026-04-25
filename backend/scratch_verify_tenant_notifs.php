<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

Config::set('database.connections.tenant.database', 'geny_hp');
DB::purge('tenant');

$notifs = DB::connection('tenant')->table('notifications')->get();
echo "Notifications in Tenant geny_hp:\n";
foreach ($notifs as $n) {
    echo "ID: {$n->id}, Type: {$n->type}, Data: {$n->data}\n";
}
