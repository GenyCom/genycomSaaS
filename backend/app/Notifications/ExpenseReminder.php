<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Depense;

class ExpenseReminder extends Notification
{
    use Queueable;

    public function __construct(public Depense $depense, public string $prochaineDate)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'depense_id' => $this->depense->id,
            'libelle' => $this->depense->libelle,
            'montant' => $this->depense->montant,
            'frequence' => $this->depense->frequence,
            'prochaine_echeance' => $this->prochaineDate,
            'message' => "Rappel : La dépense récurrente '{$this->depense->libelle}' est prévue pour le " . date('d/m/Y', strtotime($this->prochaineDate)) . ".",
            'type' => 'expense_reminder'
        ];
    }
}
