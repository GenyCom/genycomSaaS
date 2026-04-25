<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Services\FacturationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class SimulateRecurringInvoices extends Command
{
    protected $signature = 'factures:simuler-recurrentes {date? : La date de simulation (YYYY-MM-DD), par défaut demain}';
    protected $description = 'Simule la génération des factures récurrentes pour voir ce qui sera facturé à une date donnée.';

    public function __construct(private FacturationService $facturationService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $dateStr = $this->argument('date') ?: now()->addDay()->toDateString();
        $date = Carbon::parse($dateStr);

        $this->info("Simulation de la facturation récurrente pour le : {$date->format('d/m/Y')}");

        $tenants = Tenant::where('statut', 'actif')->get();
        $totalContrats = 0;
        $totalMontant = 0;

        foreach ($tenants as $tenant) {
            $this->line("\nTraitement du locataire : {$tenant->nom}");
            
            Config::set('database.connections.tenant.database', $tenant->database_name);
            DB::purge('tenant');

            $contrats = $this->facturationService->simulerEcheances($date->toDateString());

            if ($contrats->isEmpty()) {
                $this->line("   > Aucun contrat à échéance.");
                continue;
            }

            $headers = ['Client', 'Titre', 'Échéance', 'Fréquence', 'Total TTC'];
            $rows = [];

            foreach ($contrats as $contrat) {
                $rows[] = [
                    $contrat->client->societe ?? $contrat->client->display_name,
                    $contrat->titre,
                    $contrat->prochaine_echeance->format('d/m/Y'),
                    $contrat->frequence,
                    number_format($contrat->total_ttc, 2) . ' DH'
                ];
                $totalContrats++;
                $totalMontant += $contrat->total_ttc;
            }

            $this->table($headers, $rows);
        }

        $this->info("\n--- Résumé de la simulation ---");
        $this->info("Total de contrats à facturer : {$totalContrats}");
        $this->info("Montant total estimé : " . number_format($totalMontant, 2) . " DH");
    }
}