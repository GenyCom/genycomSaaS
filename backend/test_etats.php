<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Tenant::find(1);
\Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $tenant->database_name);
\Illuminate\Support\Facades\DB::purge('tenant');

$request = new \Illuminate\Http\Request();
$controller = app(App\Http\Controllers\Api\DevisController::class);
$response = $controller->index($request);
echo $response->getContent();
