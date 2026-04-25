<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;

// Nettoyer si elle existe déjà
Illuminate\Support\Facades\DB::statement("DROP DATABASE IF EXISTS genycom_acme");
Illuminate\Support\Facades\DB::connection('central')->table('tenants')->where('database_name', 'genycom_acme')->delete();
Illuminate\Support\Facades\DB::connection('central')->table('users')->where('email', 'admin@acme.com')->delete();

echo "Testing Provisioning Service directly...\n";

$service = app(TenantProvisioningService::class);
$data = [
    'nom_entreprise'  => 'Acme Corporation Test',
    'database_name'   => 'genycom_acme',
    'email'           => 'admin@acme.com',
    'prenom'          => 'Admin',
    'nom'             => 'Acme',
    'password'        => 'password123',
    'adresse'         => '123 Fake Street',
    'ville'           => 'Rabat',
    'telephone'       => '0537000000',
    'forme_juridique' => 'SA',
    'site_web'        => 'https://acme.test',
];

try {
    $result = $service->provisionner($data);
    echo "SUCCESS!\n";
    echo "Tenant ID: " . $result['tenant']->id . "\n";
    echo "User ID: " . $result['user']->id . "\n";
    
    // Verifiy the DB was created and data inserted
    config(['database.connections.tenant.database' => 'genycom_acme']);
    $entreprise = Illuminate\Support\Facades\DB::connection('tenant')->table('entreprise')->first();
    echo "Entreprise in Tenant DB: " . json_encode($entreprise) . "\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
