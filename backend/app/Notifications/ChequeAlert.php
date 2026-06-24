<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChequeAlert extends Notification
{
    use Queueable;

    public function __construct(
        public int $chequeId,
        public string $numeroCheque,
        public string $banque,
        public float $montant,
        public string $dueDate,
        public string $type = 'cheque_due_soon' // cheque_due_soon or cheque_overdue
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $title = $this->type === 'cheque_overdue' 
            ? "Chèque en retard d'encaissement" 
            : "Échéance de chèque proche";

        $message = $this->type === 'cheque_overdue'
            ? "Le chèque n° {$this->numeroCheque} ({$this->banque}) d'un montant de {$this->montant} DH est en retard d'encaissement depuis le {$this->dueDate}."
            : "Le chèque n° {$this->numeroCheque} ({$this->banque}) d'un montant de {$this->montant} DH arrive à échéance le {$this->dueDate}.";

        return [
            'cheque_id' => $this->chequeId,
            'numero_cheque' => $this->numeroCheque,
            'banque' => $this->banque,
            'montant' => $this->montant,
            'date_echeance' => $this->dueDate,
            'title' => $title,
            'message' => $message,
            'type' => $this->type
        ];
    }
}
