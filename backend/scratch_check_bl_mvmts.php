<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MouvementStock;
use App\Models\BonLivraison;

$lastBL = BonLivraison::latest()->first();
if (!$lastBL) {
    echo "Aucun BL trouvé\n";
    exit;
}

echo "Dernier BL: {$lastBL->numero} (ID: {$lastBL->id}), Entrepot: {$lastBL->entrepot_id}\n";

$mouvements = MouvementStock::with(['stock.entrepot'])
    ->where('document_type', 'BL')
    ->where('document_id', $lastBL->id)
    ->get();

foreach ($mouvements as $m) {
    echo "Mouvement ID: {$m->id}, Produit ID: {$m->produit_id}, Qty: {$m->quantite}, Entrepot: " . ($m->stock->entrepot->nom ?? 'N/A') . " (ID: {$m->stock->entrepot_id}), Libelle: {$m->libelle}\n";
}
