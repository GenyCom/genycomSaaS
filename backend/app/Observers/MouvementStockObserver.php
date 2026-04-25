<?php

namespace App\Observers;

use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use App\Notifications\StockAlert;
use App\Models\Tenant;

class MouvementStockObserver
{
    /**
     * Gérer l'événement "créé" du mouvement de stock.
     */
    public function created(MouvementStock $mouvement)
    {
        $produit = $mouvement->produit;
        if (!$produit || $produit->is_service) return;

        // 1. Mettre à jour le stock cumulé sur le produit
        // On recalcule le stock total à partir de tous les entrepôts pour être sûr
        $totalStock = Stock::where('produit_id', $produit->id)->sum('quantite');
        
        $produit->update(['stock_actuel' => $totalStock]);

        // 2. Vérifier les seuils d'alerte
        $this->checkAlerts($produit, $totalStock);
    }

    private function checkAlerts(Produit $produit, float $totalStock)
    {
        // On récupère le tenant pour envoyer les notifications aux bonnes personnes
        $tenant = Tenant::find($produit->tenant_id);
        if (!$tenant) return;

        $type = null;
        if ($totalStock <= 0) {
            $type = 'rupture';
        } elseif ($totalStock <= $produit->seuil_alerte) {
            $type = 'alerte';
        }

        if ($type) {
            // Envoyer la notification aux admins
            $admins = $tenant->users()->wherePivot('role_id', 1)->get();
            
            foreach ($admins as $admin) {
                // Éviter les notifications répétitives pour le même produit dans la même heure
                $recentNotif = $admin->unreadNotifications()
                    ->where('data->produit_id', $produit->id)
                    ->where('data->type', 'stock_alert')
                    ->where('created_at', '>', now()->subHour())
                    ->exists();

                if (!$recentNotif) {
                    $admin->notify(new StockAlert($produit, $totalStock, $type));
                }
            }
        }
    }
}
