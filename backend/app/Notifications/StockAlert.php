<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Produit;

class StockAlert extends Notification
{
    use Queueable;

    public function __construct(public Produit $produit, public float $stockActuel, public string $type = 'alerte')
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $title = $this->type === 'rupture' ? "Rupture de Stock" : "Alerte Stock";
        $message = $this->type === 'rupture' 
            ? "Le produit '{$this->produit->designation}' est épuisé (Stock: 0)."
            : "Le produit '{$this->produit->designation}' est sous le seuil d'alerte ({$this->stockActuel} restant).";

        return [
            'produit_id' => $this->produit->id,
            'designation' => $this->produit->designation,
            'reference' => $this->produit->reference,
            'stock_actuel' => $this->stockActuel,
            'seuil_alerte' => $this->produit->seuil_alerte,
            'title' => $title,
            'message' => $message,
            'type' => 'stock_alert',
            'alert_level' => $this->type
        ];
    }
}
