<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\Stock;

$tenant = Tenant::where('statut', 'actif')->first();
Config::set('database.connections.tenant.database', $tenant->database_name);
DB::purge('tenant');

echo "Synchronisation des stocks pour {$tenant->nom}...\n";

$stocks = Stock::all();
foreach ($stocks as $stock) {
    // Calculer la somme réelle des mouvements pour ce stock précis
    $realQty = DB::connection('tenant')->table('mouvements_stock')
        ->where('stock_id', $stock->id)
        ->get()
        ->sum(function($m) {
            $isEntree = in_array($m->type_mouvement, ['entree_achat', 'entree_retour', 'ajustement_positif', 'transfert_in']);
            return $isEntree ? $m->quantite : -$m->quantite;
        });

    echo "Produit {$stock->produit_id} | Entrepot {$stock->entrepot_id} : {$stock->quantite} -> {$realQty}\n";
    
    $stock->quantite = $realQty;
    $stock->save();
    
    // Mettre à jour aussi le stock_actuel dans la table produits
    DB::connection('tenant')->table('produits')
        ->where('id', $stock->produit_id)
        ->update(['stock_actuel' => $realQty]);
}

echo "Terminé.";
