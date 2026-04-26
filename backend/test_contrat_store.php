<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/contrats', 'POST', [
    'client_id' => 1,
    'titre' => 'Test',
    'date_debut' => '2026-04-26',
    'frequence' => 'MENSUEL',
    'lignes' => [
        [
            'designation' => 'Test',
            'quantite' => 1,
            'prix_unitaire' => 100
        ]
    ]
]);

$tenant = App\Models\Tenant::find(1);
$request->merge(['current_tenant' => $tenant]);
\Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $tenant->database_name);
\Illuminate\Support\Facades\DB::purge('tenant');

try {
    $controller = app()->make(App\Http\Controllers\Api\ContratController::class);
    $response = $controller->store($request);
    echo $response->getContent();
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
