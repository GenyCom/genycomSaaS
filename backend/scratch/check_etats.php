<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EtatDocument;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

$tenant = Tenant::find(3);
if ($tenant) {
    $tenant->configure(); // Méthode supposée configurer la DB du tenant
}

$etats = EtatDocument::where('tenant_id', 3)->get();
foreach($etats as $e) {
    echo "ID: {$e->id} | Type: {$e->type_document} | Code: {$e->code} | Libelle: {$e->libelle}\n";
}
