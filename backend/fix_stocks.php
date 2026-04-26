<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = App\Models\Tenant::all();

foreach ($tenants as $tenant) {
    if (!$tenant->database_name) continue;
    \Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $tenant->database_name);
    \Illuminate\Support\Facades\DB::purge('tenant');
    
    $nullStocks = App\Models\Stock::whereNull('entrepot_id')->get();
    echo 'Tenant ' . $tenant->id . ': Found ' . count($nullStocks) . ' null stocks.' . PHP_EOL;
    
    foreach ($nullStocks as $stock) {
        $defaultEntrepot = App\Models\Entrepot::where('tenant_id', $stock->tenant_id)->where('is_default', true)->first();
        
        if (!$defaultEntrepot) {
            // S'il n'y a pas d'entrepôt par défaut, on en crée un
            $defaultEntrepot = App\Models\Entrepot::create([
                'tenant_id' => $stock->tenant_id,
                'code' => 'DEP-PPAL',
                'nom' => 'Dépôt Principal',
                'is_default' => true
            ]);
        }
        
        $existing = App\Models\Stock::where('produit_id', $stock->produit_id)->where('entrepot_id', $defaultEntrepot->id)->first();
        
        if ($existing) {
            $existing->quantite += $stock->quantite;
            $existing->saveQuietly();
            App\Models\MouvementStock::where('stock_id', $stock->id)->update(['stock_id' => $existing->id]);
            $stock->delete();
            echo '  - Merged product ' . $stock->produit_id . PHP_EOL;
        } else {
            $stock->entrepot_id = $defaultEntrepot->id;
            $stock->saveQuietly();
            echo '  - Moved product ' . $stock->produit_id . PHP_EOL;
        }
    }
}
echo "Correction terminee.\n";
