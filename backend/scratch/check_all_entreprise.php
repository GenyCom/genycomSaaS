<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

foreach(Tenant::all() as $tenant) {
    echo "--- Tenant ID: {$tenant->id} ({$tenant->nom}) ---\n";
    $tenant->configure();
    try {
        $entreprise = DB::connection('tenant')->table('entreprise')->first();
        if ($entreprise) {
            echo "Raison Sociale: " . ($entreprise->raison_sociale ?? 'NULL') . "\n";
            echo "Email: " . ($entreprise->email ?? 'NULL') . "\n";
            echo "Logo: " . ($entreprise->logo_path ?? 'NULL') . "\n";
        } else {
            echo "No entreprise entry found.\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
