<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Facture;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'factures:check-overdue';
    protected $description = 'Vérifie les factures impayées et génère des alertes de retard.';

    public function handle()
    {
        $tenants = Tenant::where('statut', 'actif')->get();

        foreach ($tenants as $tenant) {
            $this->info("Vérification des factures pour {$tenant->nom}...");
            Config::set('database.connections.tenant.database', $tenant->database_name);
            DB::purge('tenant');

            // 1. Récupérer les factures non réglées dont l'échéance est passée
            $overdueFactures = DB::connection('tenant')->table('factures')
                ->where('tenant_id', $tenant->id)
                ->where('est_reglee', false)
                ->where('date_echeance', '<', Carbon::today())
                ->whereNull('deleted_at')
                ->get();

            foreach ($overdueFactures as $facture) {
                $daysOverdue = Carbon::parse($facture->date_echeance)->diffInDays(Carbon::today());
                
                // On notifie dès le jour même (0) puis à des intervalles précis (J+7, J+15)
                if (in_array($daysOverdue, [0, 1, 7, 15, 30])) {
                    $this->createNotification($tenant->id, $facture, $daysOverdue);
                }
            }
        }
        $this->info("Vérification terminée.");
    }

    private function createNotification($tenantId, $facture, $days)
    {
        // On vérifie si une notification identique n'a pas déjà été créée aujourd'hui
        $exists = DB::connection('tenant')->table('notifications')
            ->where('type', 'facture_overdue')
            ->where('data->facture_id', $facture->id)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if (!$exists) {
            DB::connection('tenant')->table('notifications')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'facture_overdue',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $facture->created_by ?? 1,
                'data' => json_encode([
                    'title' => "Facture en retard",
                    'message' => "La facture {$facture->numero} est en retard de {$days} jours.",
                    'facture_id' => $facture->id,
                    'client_id' => $facture->client_id,
                    'type' => 'facture_overdue'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
