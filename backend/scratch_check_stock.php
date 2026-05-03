<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Stock;
use App\Models\Produit;

$produit = Produit::where('designation', 'LIKE', '%MS SQL%')->first();
if (!$produit) {
    echo "Produit non trouvé\n";
    exit;
}

echo "Produit ID: {$produit->id}, Designation: {$produit->designation}, Stock Actuel: {$produit->stock_actuel}\n";

$stocks = Stock::with('entrepot')->where('produit_id', $produit->id)->get();
foreach ($stocks as $s) {
    echo "Stock ID: {$s->id}, Entrepot: " . ($s->entrepot->nom ?? 'N/A') . " (ID: {$s->entrepot_id}), Qty: {$s->quantite}\n";
}
