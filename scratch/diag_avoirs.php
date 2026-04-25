<?php
require __DIR__.'/../backend/vendor/autoload.php';
$app = require_once __DIR__.'/../backend/bootstrap/app.php';
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// On force la connexion à la base tenant Sorec (geny_hp) pour le test
Config::set('database.connections.tenant.database', 'geny_hp');
DB::purge('tenant');

use App\Models\AvoirFournisseur;
use App\Models\User;

$user = User::find(2);
$tenantId = $user->tenant_id;

echo "User ID: 2\n";
echo "User Tenant ID: " . ($tenantId ?? 'NULL') . "\n";

$allAvoirs = AvoirFournisseur::all();
echo "Nombre total d'avoirs dans la table (tous tenants) : " . $allAvoirs->count() . "\n";

foreach($allAvoirs as $a) {
    echo "- ID: {$a->id}, Numero: {$a->numero}, Tenant ID: {$a->tenant_id}\n";
}

$filteredAvoirs = AvoirFournisseur::where('tenant_id', $tenantId)->get();
echo "Nombre d'avoirs pour votre Tenant ($tenantId) : " . $filteredAvoirs->count() . "\n";
