<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Produit;
use App\Models\Stock;
use App\Notifications\StockAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CheckStockAlerts extends Command
{
    protected $signature = 'stock:check-alerts';
    protected $description = 'Synchronise le stock actuel des produits et génère des alertes de réapprovisionnement.';

    public function handle()
    {
        $this->info("Démarrage du scan global des stocks...");

        $tenants = Tenant::where('statut', 'actif')->get();

        foreach ($tenants as $tenant) {
            $this->info("Traitement du locataire : {$tenant->nom}");
            
            Config::set('database.connections.tenant.database', $tenant->database_name);
            DB::purge('tenant');

            // On ne traite que les produits physiques (pas les services)
            $produits = Produit::where('is_service', false)->get();

            foreach ($produits as $produit) {
                // 1. Recalcul du stock total (somme de tous les entrepôts)
                $totalStock = Stock::where('produit_id', $produit->id)->sum('quantite');
                
                // 2. Mise à jour du cache sur le produit
                $produit->update(['stock_actuel' => $totalStock]);

                // 3. Détection du type d'alerte
                $type = null;
                if ($totalStock <= 0) {
                    $type = 'rupture';
                } elseif ($totalStock <= $produit->seuil_alerte) {
                    $type = 'alerte';
                }

                if ($type) {
                    $admins = $tenant->users()->wherePivot('role_id', 1)->get();
                    
                    foreach ($admins as $admin) {
                        // On vérifie si une notification identique n'existe pas déjà (non lue)
                        $exists = $admin->unreadNotifications()
                            ->where('data->produit_id', $produit->id)
                            ->where('data->type', 'stock_alert')
                            ->exists();

                        if (!$exists) {
                            $admin->notify(new StockAlert($produit, $totalStock, $type));
                            $this->info("  ✔ Alerte générée pour '{$produit->designation}' ({$totalStock} en stock)");
                        }
                    }
                }
            }
        }

        $this->info("Scan terminé.");
    }
}
