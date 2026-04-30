<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Stock;
use App\Models\Entrepot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class MergeOrphanStocks extends Command
{
    protected $signature = 'stock:merge-orphans';
    protected $description = 'Fusionne proprement les stocks sans entrepôt vers l\'entrepôt par défaut.';

    public function handle()
    {
        $tenants = Tenant::where('statut', 'actif')->get();

        foreach ($tenants as $tenant) {
            $this->info("Fusion pour {$tenant->nom}...");
            $tenant->configure();

            $defaultEntrepot = Entrepot::where('is_default', true)->first();
            if (!$defaultEntrepot) {
                $this->error("  Pas d'entrepôt par défaut.");
                continue;
            }

            $orphans = Stock::whereNull('entrepot_id')->get();
            foreach ($orphans as $orphan) {
                // On cherche si une ligne existe déjà pour ce produit dans le dépôt par défaut
                $existing = Stock::where('produit_id', $orphan->produit_id)
                                 ->where('entrepot_id', $defaultEntrepot->id)
                                 ->first();

                if ($existing) {
                    // 1. Rerouter les mouvements de stock vers la ligne existante
                    DB::connection('tenant')->table('mouvements_stock')
                        ->where('stock_id', $orphan->id)
                        ->update(['stock_id' => $existing->id]);

                    // 2. Fusionner les quantités
                    $existing->increment('quantite', $orphan->quantite);

                    // 3. Supprimer l'orphelin
                    $orphan->delete();
                    $this->info("  Produit {$orphan->produit_id} fusionné.");
                } else {
                    // Si pas de doublon, on change juste l'entrepôt
                    $orphan->update(['entrepot_id' => $defaultEntrepot->id]);
                    $this->info("  Produit {$orphan->produit_id} assigné au dépôt principal.");
                }
            }
        }
        
        DB::disconnect('tenant');
        DB::disconnect('central');
        
        $this->info("Opération terminée.");
    }
}
