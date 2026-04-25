<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

$tenant = Tenant::where('statut', 'actif')->first();
Config::set('database.connections.tenant.database', $tenant->database_name);
DB::purge('tenant');

echo "Historique des mouvements pour Produit 3 (Peinture Gris Argent):\n";
$mouvements = DB::connection('tenant')->table('mouvements_stock')
    ->where('produit_id', 3)
    ->orderBy('created_at')
    ->get();

$total = 0;
foreach ($mouvements as $m) {
    $isEntree = in_array($m->type_mouvement, ['entree_achat', 'entree_retour', 'ajustement_positif', 'transfert_in']);
    $qty = $isEntree ? $m->quantite : -$m->quantite;
    $total += $qty;
    echo "ID: {$m->id} | Date: {$m->created_at} | Type: {$m->type_mouvement} | Qty: {$qty} | Cumul: {$total}\n";
}

echo "\nQuantité actuelle en table stocks:\n";
$stocks = DB::connection('tenant')->table('stocks')->where('produit_id', 3)->get();
foreach ($stocks as $s) {
    echo "ID Stock: {$s->id} | Entrepot: {$s->entrepot_id} | Quantité: {$s->quantite}\n";
}
