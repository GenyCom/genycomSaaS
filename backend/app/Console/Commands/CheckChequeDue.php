<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Notifications\ChequeAlert;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckChequeDue extends Command
{
    protected $signature = 'cheques:check-due {--tenant= : ID du locataire spécifique à vérifier}';
    protected $description = 'Vérifie les échéances des chèques en attente et génère des alertes de rappel.';

    public function handle()
    {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            $tenants = Tenant::where('id', $tenantId)->get();
        } else {
            $tenants = Tenant::where('statut', 'actif')->get();
        }

        foreach ($tenants as $tenant) {
            $this->info("Vérification des chèques pour {$tenant->nom}...");
            $tenant->configure();

            // Récupérer les chèques en attente d'encaissement avec date d'échéance définie
            $pendingCheques = DB::connection('tenant')->table('reglements as r')
                ->leftJoin('mode_reglement as mr', 'mr.id', '=', 'r.mode_reglement_id')
                ->where('r.tenant_id', $tenant->id)
                ->where(function($q) {
                    $q->where('r.statut_cheque', 'en_attente')
                      ->orWhereNull('r.statut_cheque');
                })
                ->where(function($q) {
                    $q->whereNotNull('r.numero_cheque')
                      ->where('r.numero_cheque', '!=', '')
                      ->orWhere('mr.libelle', 'like', '%Chèque%')
                      ->orWhere('mr.libelle', 'like', '%Cheque%');
                })
                ->whereNotNull('r.date_echeance_cheque')
                ->select('r.id', 'r.numero_cheque', 'r.banque', 'r.date_echeance_cheque', 'r.montant', 'r.payable_type', 'r.payable_id', 'r.created_by')
                ->get();

            foreach ($pendingCheques as $cheque) {
                $dueDate = Carbon::parse($cheque->date_echeance_cheque);
                $today = Carbon::today();
                $diffInDays = $today->diffInDays($dueDate, false); // Négatif si en retard (due date dans le passé)

                $type = null;
                $title = null;
                $message = null;

                if ($diffInDays < 0) {
                    // Chèque en retard d'encaissement
                    $type = 'cheque_overdue';
                    $title = "Chèque en retard d'encaissement";
                    $message = "Le chèque n° {$cheque->numero_cheque} ({$cheque->banque}) d'un montant de {$cheque->montant} DH est en retard d'encaissement depuis le " . $dueDate->format('d/m/Y') . ".";
                } elseif ($diffInDays <= 2) {
                    // Chèque arrivant à échéance bientôt (<= 2 jours)
                    $type = 'cheque_due_soon';
                    $title = "Échéance de chèque proche";
                    $message = "Le chèque n° {$cheque->numero_cheque} ({$cheque->banque}) d'un montant de {$cheque->montant} DH arrive à échéance le " . $dueDate->format('d/m/Y') . ".";
                }

                if ($type) {
                    $admins = $tenant->users()->wherePivot('role_id', 1)->get();
                    foreach ($admins as $admin) {
                        $this->createNotification($admin, $cheque, $type, $title, $message, $dueDate->format('d/m/Y'));
                    }
                }
            }
        }

        DB::disconnect('tenant');
        DB::disconnect('central');

        $this->info("Vérification terminée.");
    }

    private function createNotification($admin, $cheque, $type, $title, $message, $dueDateFormatted)
    {
        // Éviter d'avoir des notifications en double pour le même chèque et le même type
        $exists = $admin->unreadNotifications()
            ->where('data->cheque_id', $cheque->id)
            ->where('data->type', $type)
            ->exists();

        if (!$exists) {
            $admin->notify(new ChequeAlert(
                $cheque->id,
                $cheque->numero_cheque ?? '',
                $cheque->banque ?? '',
                (float)$cheque->montant,
                $dueDateFormatted,
                $type
            ));
            $this->info("  ✔ Notification '{$type}' envoyée à {$admin->email} pour le chèque n° {$cheque->numero_cheque}");
        }
    }
}

