<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

Config::set('database.connections.tenant.database', 'geny_hp');
DB::purge('tenant');

DB::connection('tenant')->table('depenses')->update(['derniere_notification_at' => null]);
echo "Suivi réinitialisé pour le tenant hp.\n";
