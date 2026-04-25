<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class CheckQuoteRelances extends Command
{
    protected $signature = 'devis:check-relances';
    protected $description = 'Vérifie les devis en attente et suggère une relance commerciale.';

    public function handle()
    {
        $tenants = Tenant::where('statut', 'actif')->get();

        foreach ($tenants as $tenant) {
            $this->info("Vérification des devis pour {$tenant->nom}...");
            Config::set('database.connections.tenant.database', $tenant->database_name);
            DB::purge('tenant');

            // On cherche les devis non facturés et non refusés datant d'il y a 5, 10 ou 15 jours
            $devisToRelance = DB::connection('tenant')->table('devis')
                ->where('tenant_id', $tenant->id)
                ->where('est_facture', false)
                ->whereNull('deleted_at')
                ->whereIn(DB::raw('DATEDIFF(CURDATE(), date_devis)'), [5, 10, 20])
                ->get();

            foreach ($devisToRelance as $devis) {
                // On vérifie si l'état n'est pas déjà 'ACCEPTE' ou 'REFUSE'
                $etat = DB::connection('tenant')->table('etat_document')->where('id', $devis->etat_id)->first();
                if ($etat && !in_array($etat->code, ['ACCEPTE', 'REFUSE'])) {
                    $this->createNotification($tenant->id, $devis);
                }
            }
        }
        $this->info("Relances terminées.");
    }

    private function createNotification($tenantId, $devis)
    {
        $exists = DB::connection('tenant')->table('notifications')
            ->where('type', 'devis_relance')
            ->where('data->devis_id', $devis->id)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if (!$exists) {
            DB::connection('tenant')->table('notifications')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'devis_relance',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $devis->created_by ?? 1,
                'data' => json_encode([
                    'title' => "Relance Devis",
                    'message' => "Le devis {$devis->numero} est en attente. Pensez à relancer le client.",
                    'devis_id' => $devis->id,
                    'client_id' => $devis->client_id,
                    'type' => 'devis_relance'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
