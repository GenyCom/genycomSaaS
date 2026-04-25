<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BonCommandeFournisseur;
use Illuminate\Support\Facades\DB;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo "--- Tenant: {$tenant->nom} ({$tenant->database_name}) ---\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    DB::reconnect('tenant');

    try {
        $bcf = BonCommandeFournisseur::first();
        if ($bcf) {
            echo "    [OK] BCF #{$bcf->id} ({$bcf->numero}) chargé.\n";
            $count = $bcf->lignes()->count();
            echo "    [OK] Lignes chargées: {$count}\n";
            break; // Un seul suffit pour valider la relation
        } else {
            echo "    Aucun BCF.\n";
        }
    } catch (\Exception $e) {
        echo "    [!!] ERREUR : " . $e->getMessage() . "\n";
    }
}
