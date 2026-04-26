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
    
    // Recalcul de TOUT le stock basé sur la somme stricte des mouvements.
    $stocks = App\Models\Stock::all();
    foreach ($stocks as $stock) {
        // La quantité d'un stock devrait être la somme des mouvements rattachés à ce produit ET cet entrepôt.
        // Puisque tout mouvement est rattaché au stock_id maintenant, c'est encore plus simple :
        $mouvements = App\Models\MouvementStock::where('stock_id', $stock->id)->get();
        $realQuantite = 0;
        
        foreach($mouvements as $m) {
            if (in_array($m->type_mouvement, ['entree_achat', 'entree_retour', 'ajustement_positif', 'transfert_in', 'inventaire_initial'])) {
                $realQuantite += $m->quantite;
            } elseif (in_array($m->type_mouvement, ['sortie_vente', 'sortie_retour', 'ajustement_negatif', 'transfert_out'])) {
                $realQuantite -= $m->quantite;
            }
        }
        
        if ($stock->quantite != $realQuantite) {
            echo "Tenant {$tenant->id} | Produit {$stock->produit_id} : Stock corrigé de {$stock->quantite} à {$realQuantite}\n";
            $stock->quantite = $realQuantite;
            $stock->saveQuietly();
        }
    }
}
echo "Synchronisation terminee.\n";
