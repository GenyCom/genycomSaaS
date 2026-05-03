<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportingService
{
    private function tid(): int
    {
        return request()->get('current_tenant')->id ?? auth()->user()->tenant_id ?? 1;
    }

    /**
     * Rapport de ventes détaillé
     */
    public function salesJournal(string $start, string $end, ?int $clientId = null): array
    {
        return DB::connection('tenant')->table('factures as f')
            ->join('clients as c', 'c.id', '=', 'f.client_id')
            ->where('f.tenant_id', $this->tid())
            ->whereNull('f.deleted_at')
            ->whereBetween('f.date_facture', [$start, $end])
            ->when($clientId, fn($q) => $q->where('f.client_id', $clientId))
            ->select('f.id', 'f.numero', 'f.date_facture', 'c.societe', 'f.total_ht', 'f.total_tva', 'f.total_ttc', 'f.montant_regle', 'f.est_reglee')
            ->orderBy('f.date_facture', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Rapport d'achats et dépenses détaillé
     */
    public function purchaseJournal(string $start, string $end, ?int $supplierId = null): array
    {
        $achats = DB::connection('tenant')->table('factures_achats as fa')
            ->join('fournisseurs as fr', 'fr.id', '=', 'fa.fournisseur_id')
            ->where('fa.tenant_id', $this->tid())
            ->whereNull('fa.deleted_at')
            ->whereBetween('fa.date_facture', [$start, $end])
            ->when($supplierId, fn($q) => $q->where('fa.fournisseur_id', $supplierId))
            ->select('fa.id', 'fa.numero', 'fa.date_facture', 'fr.societe', 'fa.montant_ht as total_ht', 'fa.montant_tva as total_tva', 'fa.montant_ttc as total_ttc', DB::raw("'achat' as type"))
            ->get();

        $depenses = DB::connection('tenant')->table('depenses as d')
            ->where('d.tenant_id', $this->tid())
            ->whereNull('d.deleted_at')
            ->whereBetween('d.date_depense', [$start, $end])
            // Les dépenses directes n'ont pas forcément de fournisseur_id dans ce modèle simple,
            // mais on peut les filtrer si on avait un lien. Pour l'instant, si un fournisseur est choisi, on ne montre que les achats.
            ->when($supplierId, fn($q) => $q->whereRaw('1=0')) 
            ->select('d.id', DB::raw("'' as numero"), 'd.date_depense as date_facture', 'd.libelle as societe', 'd.montant as total_ht', DB::raw("0 as total_tva"), 'd.montant as total_ttc', DB::raw("'depense' as type"))
            ->get();

        return $achats->concat($depenses)->sortByDesc('date_facture')->values()->toArray();
    }

    /**
     * Analyse du CA par Client
     */
    public function salesByClient(string $start, string $end): array
    {
        return DB::connection('tenant')->table('factures as f')
            ->join('clients as c', 'c.id', '=', 'f.client_id')
            ->where('f.tenant_id', $this->tid())
            ->whereNull('f.deleted_at')
            ->whereBetween('f.date_facture', [$start, $end])
            ->selectRaw('c.societe, COUNT(f.id) as nb_factures, SUM(f.total_ht) as total_ht, SUM(f.total_ttc) as total_ttc')
            ->groupBy('c.id', 'c.societe')
            ->orderByDesc('total_ttc')
            ->get()
            ->toArray();
    }

    /**
     * Rentabilité par Projet
     */
    public function profitabilityByProject(string $start, string $end): array
    {
        return DB::connection('tenant')->table('factures as f')
            ->join('projets as p', 'p.id', '=', 'f.projet_id')
            ->where('f.tenant_id', $this->tid())
            ->whereNull('f.deleted_at')
            ->whereBetween('f.date_facture', [$start, $end])
            ->selectRaw('p.nom_projet, SUM(f.total_ht) as revenue_ht, SUM(f.total_ttc) as revenue_ttc')
            ->groupBy('p.id', 'p.nom_projet')
            ->orderByDesc('revenue_ht')
            ->get()
            ->toArray();
    }

    /**
     * Rapport de TVA (Collectée vs Déductible)
     */
    public function vatReport(string $start, string $end): array
    {
        $collected = DB::connection('tenant')->table('factures')
            ->where('tenant_id', $this->tid())
            ->whereNull('deleted_at')
            ->whereBetween('date_facture', [$start, $end])
            ->sum('total_tva');

        $deductibleAchats = DB::connection('tenant')->table('factures_achats')
            ->where('tenant_id', $this->tid())
            ->whereNull('deleted_at')
            ->whereBetween('date_facture', [$start, $end])
            ->sum('montant_tva');

        $deductibleDepenses = 0; // La table depenses semble ne pas avoir de colonne TVA distincte d'après le modèle

        return [
            'collected_vat' => (float) $collected,
            'deductible_vat' => (float) $deductibleAchats,
            'net_vat' => (float) ($collected - $deductibleAchats)
        ];
    }

    /**
     * Valorisation du Stock
     */
    public function inventoryValuation(): array
    {
        $res = DB::connection('tenant')->table('produits')
            ->where('tenant_id', $this->tid())
            ->whereNull('deleted_at')
            ->where('is_service', 0)
            ->selectRaw('SUM(stock_actuel * prix_ht_achat) as total_value_purchase, SUM(stock_actuel * prix_ht_vente) as total_value_sale')
            ->first();

        return (array) $res;
    }

    /**
     * État des règlements (Journal de caisse/banque)
     */
    public function paymentsJournal(string $start, string $end): array
    {
        $reglements = DB::connection('tenant')->table('reglements as r')
            ->leftJoin('mode_reglement as mr', 'mr.id', '=', 'r.mode_reglement_id')
            ->where('r.tenant_id', $this->tid())
            ->whereBetween('r.date_reglement', [$start, $end])
            ->select('r.id', 'r.date_reglement as date', 'r.montant', 'r.payable_type', 'r.payable_id', 'mr.libelle as mode', 'r.numero_cheque', 'r.reference_virement', 'r.observations')
            ->get();

        $data = $reglements->map(function($reg) {
            $tiers = "Inconnu";
            $refDoc = "—";
            
            if ($reg->payable_type === 'App\\Models\\Facture') {
                $facture = DB::connection('tenant')->table('factures as f')
                    ->join('clients as c', 'c.id', '=', 'f.client_id')
                    ->where('f.id', $reg->payable_id)
                    ->select('c.societe', 'f.numero')
                    ->first();
                if ($facture) {
                    $tiers = $facture->societe;
                    $refDoc = "Facture Client " . $facture->numero;
                }
            } elseif ($reg->payable_type === 'App\\Models\\DetteFournisseur') {
                $dette = DB::connection('tenant')->table('dettes_fournisseur as d')
                    ->join('fournisseurs as fr', 'fr.id', '=', 'd.fournisseur_id')
                    ->where('d.id', $reg->payable_id)
                    ->select('fr.societe', 'd.numero')
                    ->first();
                if ($dette) {
                    $tiers = $dette->societe;
                    $refDoc = "Dette/BR Fournisseur " . $dette->numero;
                }
            }

            return [
                'date' => $reg->date,
                'tiers' => $tiers,
                'ref_doc' => $refDoc,
                'montant' => (float) $reg->montant,
                'mode' => $reg->mode ?? 'Espèces',
                'reference' => $reg->numero_cheque ?: $reg->reference_virement ?: '—',
                'observations' => $reg->observations,
                'flux' => (str_contains($reg->payable_type, 'Facture') ? 'Entrée' : 'Sortie')
            ];
        });

        // Ajouter aussi les paiements directs de factures d'achat si ils existent dans paiements_fournisseurs
        $paiementsAchats = DB::connection('tenant')->table('paiements_fournisseurs as pf')
            ->join('factures_achats as fa', 'fa.id', '=', 'pf.facture_achat_id')
            ->join('fournisseurs as fr', 'fr.id', '=', 'fa.fournisseur_id')
            ->where('pf.tenant_id', $this->tid())
            ->whereBetween('pf.date_paiement', [$start, $end])
            ->select('pf.date_paiement as date', 'pf.montant', 'fr.societe', 'fa.numero', 'pf.mode_paiement as mode', 'pf.reference', 'pf.observations')
            ->get()
            ->map(fn($p) => [
                'date' => $p->date,
                'tiers' => $p->societe,
                'ref_doc' => "Facture Achat " . $p->numero,
                'montant' => (float) $p->montant,
                'mode' => $p->mode,
                'reference' => $p->reference ?: '—',
                'observations' => $p->observations,
                'flux' => 'Sortie'
            ]);

        return $data->concat($paiementsAchats)->sortByDesc('date')->values()->toArray();
    }

    /**
     * État des factures impayées (Balance âgée simplifiée)
     */
    public function unpaidInvoices(): array
    {
        $clients = DB::connection('tenant')->table('factures as f')
            ->join('clients as c', 'c.id', '=', 'f.client_id')
            ->where('f.tenant_id', $this->tid())
            ->whereNull('f.deleted_at')
            ->where('f.est_reglee', 0)
            ->whereNotNull('f.numero')
            ->select('f.numero', 'f.date_facture', 'f.date_echeance', 'c.societe', 'f.total_ttc', 'f.montant_regle', DB::raw("(f.total_ttc - f.montant_regle) as reste_a_payer"), DB::raw("'client' as tiers_type"))
            ->get();

        $fournisseurs = DB::connection('tenant')->table('factures_achats as fa')
            ->join('fournisseurs as fr', 'fr.id', '=', 'fa.fournisseur_id')
            ->where('fa.tenant_id', $this->tid())
            ->whereNull('fa.deleted_at')
            ->where('fa.statut', '!=', 'paye')
            ->select('fa.numero', 'fa.date_facture', 'fa.date_echeance', 'fr.societe', 'fa.montant_ttc as total_ttc', 'fa.montant_paye as montant_regle', 'fa.reste_a_payer', DB::raw("'fournisseur' as tiers_type"))
            ->get();

        return [
            'clients' => $clients->toArray(),
            'fournisseurs' => $fournisseurs->toArray(),
            'total_clients' => (float) $clients->sum('reste_a_payer'),
            'total_fournisseurs' => (float) $fournisseurs->sum('reste_a_payer'),
        ];
    }
}
