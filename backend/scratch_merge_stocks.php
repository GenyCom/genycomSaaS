<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Stock;
use App\Models\Entrepot;
use App\Services\StockService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

Config::set('database.connections.tenant.database', 'geny_hp');
DB::purge('tenant');

$stockService = new StockService();
$defaultId = $stockService->getDefaultEntrepotId(1);

echo "Fusion des stocks vers l'entrepôt ID: $defaultId...\n";

// Trouver tous les stocks sans entrepôt
$stocksOrphelins = Stock::whereNull('entrepot_id')->get();

foreach ($stocksOrphelins as $orphan) {
    echo "Traitement produit ID {$orphan->produit_id} (Quantité: {$orphan->quantite})...\n";
    
    // Chercher si le produit existe déjà dans l'entrepôt par défaut
    $existing = Stock::where('produit_id', $orphan->produit_id)
                     ->where('entrepot_id', $defaultId)
                     ->first();
    
    if ($existing) {
        // Fusionner
        $existing->quantite += $orphan->quantite;
        $existing->save();
        $orphan->delete();
        echo "  -> Fusionné avec succès. Nouveau total: {$existing->quantite}\n";
    } else {
        // Juste assigner l'entrepôt
        $orphan->update(['entrepot_id' => $defaultId]);
        echo "  -> Assigné au dépôt principal.\n";
    }
}

echo "Nettoyage terminé.\n";
