<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Contrat;
use App\Services\FacturationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'factures:generer-recurrentes';
    protected $description = 'Génère les factures pour les contrats/abonnements dont l\'échéance est atteinte.';

    public function __construct(private FacturationService $facturationService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Démarrage de la génération des factures récurrentes...");

        $tenants = Tenant::where('statut', 'actif')->get();

        foreach ($tenants as $tenant) {
            $this->info("Traitement du locataire : {$tenant->nom}");
            
            Config::set('database.connections.tenant.database', $tenant->database_name);
            DB::purge('tenant');

            $contrats = Contrat::with('lignes')->where('statut', 'ACTIF')
                ->whereDate('prochaine_echeance', '<=', now()->toDateString())
                ->get();

            if ($contrats->isEmpty()) {
                continue;
            }

            foreach ($contrats as $contrat) {
                try {
                    $facture = $this->facturationService->genererDepuisContrat($contrat);
                    $this->info("✔ Facture {$facture->numero} générée pour le contrat {$contrat->titre}");
                } catch (\Exception $e) {
                    $msg = "Erreur contrat {$contrat->id} (Tenant {$tenant->nom}): " . $e->getMessage();
                    $this->error($msg);
                    Log::error($msg);
                }
            }
        }
        $this->info("Processus terminé.");
    }
}
