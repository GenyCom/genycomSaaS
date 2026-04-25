<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_hp']);
DB::purge('tenant');
$db = DB::connection('tenant');

echo "DESCRIBE etat_document (HP):\n";
print_r($db->select("DESCRIBE etat_document"));
