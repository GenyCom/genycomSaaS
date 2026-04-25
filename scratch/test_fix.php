<?php
use App\Models\Tenant;
use App\Models\Devis;
use App\Models\LigneDevis;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Get a tenant
$tenant = Tenant::first();
if (!$tenant) {
    die("No tenants found in central database\n");
}

// 2. Mock the request attribute
request()->attributes->set('current_tenant', $tenant);

// 3. Switch connection manually for testing
Config::set('database.connections.tenant.database', $tenant->database_name);
DB::purge('tenant');

echo "Testing creation for tenant: {$tenant->database_name}\n";

try {
    DB::beginTransaction();

    // Create a dummy Devis
    $devis = Devis::create([
        'numero' => 'TEST-001',
        'date_devis' => now(),
        'client_id' => 1, // Assuming client 1 exists
        'total_ht' => 100,
        'total_ttc' => 120,
    ]);

    echo "Devis created with ID: {$devis->id}, Tenant ID: {$devis->tenant_id}\n";

    // Create a dummy Ligne
    $ligne = LigneDevis::create([
        'devis_id' => $devis->id,
        'designation' => 'Produit Test',
        'quantite' => 1,
        'prix_unitaire' => 100,
        'taux_tva' => 20,
        'montant_ht' => 100,
        'montant_ttc' => 120,
        'ordre' => 1
    ]);

    echo "Ligne inserted successfully. Tenant ID in Ligne: {$ligne->tenant_id}\n";

    DB::rollBack();
    echo "Test PASSED (Rollback successful)\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Test FAILED: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
