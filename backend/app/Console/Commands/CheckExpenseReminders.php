<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Depense;
use App\Models\User;
use App\Notifications\ExpenseReminder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class CheckExpenseReminders extends Command
{
    protected $signature = 'depenses:check-reminders {--tenant= : ID du locataire spécifique à vérifier}';
    protected $description = 'Vérifie les dépenses récurrentes et envoie des notifications pour les échéances proches ou passées.';

    public function handle()
    {
        $tenantId = $this->option('tenant');
        
        if ($tenantId) {
            $tenants = Tenant::where('id', $tenantId)->get();
            $this->info("Démarrage de la vérification pour le locataire ID : {$tenantId}");
        } else {
            $tenants = Tenant::where('statut', 'actif')->get();
            $this->info("Démarrage de la vérification globale des rappels de dépenses...");
        }

        foreach ($tenants as $tenant) {
            $this->info("Traitement du locataire : {$tenant->nom}");
            
            $tenant->configure();

            $depenses = Depense::where('is_recurrente', true)->get();

            foreach ($depenses as $depense) {
                // On cherche l'échéance la plus proche de "aujourd'hui"
                $prochaineDate = $this->calculateRelevantOccurrence($depense);
                
                if (!$prochaineDate) continue;

                $this->info("  - Dépense '{$depense->libelle}' : Échéance détectée le {$prochaineDate}");

                // Vérifier si on a déjà notifié pour CETTE occurrence précise
                // Si derniere_notification_at est >= à la date du jour du rappel (ou si c'est la même occurrence), on saute
                if ($depense->derniere_notification_at && $depense->derniere_notification_at->format('Y-m-d') >= $prochaineDate) {
                    $this->line("    (Déjà notifié pour cette échéance)");
                    continue;
                }

                $diff = (strtotime($prochaineDate) - time()) / (60 * 60 * 24);
                
                // On notifie si :
                // 1. L'échéance est passée (diff < 0) -> RETARD
                // 2. L'échéance est dans moins de 3 jours -> RAPPEL
                if ($diff <= 3) {
                    $admins = $tenant->users()->wherePivot('role_id', 1)->get();
                    
                    foreach ($admins as $admin) {
                        $admin->notify(new ExpenseReminder($depense, $prochaineDate));
                        $this->info("    ✔ Notification envoyée à {$admin->email}");
                    }

                    // Mettre à jour la date de dernière notification
                    $depense->update(['derniere_notification_at' => now()]);
                }
            }
        }

        $this->info("Fin de la vérification.");
    }

    /**
     * Calcule l'échéance la plus "pertinente" par rapport à aujourd'hui.
     */
    private function calculateRelevantOccurrence(Depense $depense)
    {
        $startDate = $depense->date_depense;
        if (!$startDate) return null;

        $dt = new \DateTime($startDate->format('Y-m-d'));
        $now = new \DateTime();
        $now->setTime(0, 0, 0);

        // Si la date initiale est dans le futur, c'est elle la prochaine
        if ($dt > $now) {
            return $dt->format('Y-m-d');
        }

        // Sinon, on avance par bonds de fréquence jusqu'à arriver autour d'aujourd'hui
        while ($dt < $now) {
            $prev = clone $dt;
            
            if ($depense->frequence === 'mensuel') {
                $dt->modify('+1 month');
            } elseif ($depense->frequence === 'trimestriel') {
                $dt->modify('+3 months');
            } elseif ($depense->frequence === 'annuel') {
                $dt->modify('+1 year');
            } else {
                return null;
            }

            // Si en avançant on a dépassé "aujourd'hui", 
            // est-ce que l'occurrence précédente ($prev) était très récente ?
            // (ex: hier ou avant-hier). On la considère comme "pertinente" si elle n'a pas été traitée.
        }

        // On renvoie l'échéance la plus proche dans le passé ou le futur immédiat
        // Si $dt est aujourd'hui ou dans le futur proche, on le prend
        // Sinon on regarde si l'occurrence juste avant était très proche
        
        // Pour faire simple : on renvoie la première occurrence >= (Aujourd'hui - 7 jours)
        // Cela permet de rattraper les retards de la semaine.
        
        $dt = new \DateTime($startDate->format('Y-m-d'));
        $target = clone $now;
        $target->modify('-7 days'); // Fenêtre de rattrapage de 7 jours

        while ($dt < $target) {
            if ($depense->frequence === 'mensuel') $dt->modify('+1 month');
            elseif ($depense->frequence === 'trimestriel') $dt->modify('+3 months');
            elseif ($depense->frequence === 'annuel') $dt->modify('+1 year');
            else break;
        }

        return $dt->format('Y-m-d');
    }
}
