<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MouvementStock;
use App\Models\Stock;

$mouvements = MouvementStock::with(['stock.entrepot'])
    ->where('produit_id', 11) // ID for Licence MS SQL Server Std if possible, but I don't know it. 
    // Let's just get the last 10 movements.
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();

foreach ($mouvements as $m) {
    echo "ID: {$m->id}, Type: {$m->type_mouvement}, Qty: {$m->quantite}, Entrepot: " . ($m->stock->entrepot->nom ?? 'N/A') . ", Doc: {$m->document_type} #{$m->document_id}\n";
}
