<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Tenant::find(1);
\Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $tenant->database_name);
\Illuminate\Support\Facades\DB::purge('tenant');

$result = \Illuminate\Support\Facades\DB::connection('tenant')->select('SHOW CREATE TABLE ligne_contrat');
echo $result[0]->{'Create Table'};
