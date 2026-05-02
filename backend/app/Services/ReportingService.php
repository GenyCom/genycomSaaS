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
    public function salesJournal(string $start, string $end): array
    {
        return DB::connection('tenant')->table('factures as f')
            ->join('clients as c', 'c.id', '=', 'f.client_id')
            ->where('f.tenant_id', $this->tid())
            ->whereNull('f.deleted_at')
            ->whereBetween('f.date_facture', [$start, $end])
            ->select('f.id', 'f.numero', 'f.date_facture', 'c.societe', 'f.total_ht', 'f.total_tva', 'f.total_ttc', 'f.montant_regle', 'f.est_reglee')
            ->orderBy('f.date_facture', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Rapport d'achats et dépenses détaillé
     */
    public function purchaseJournal(string $start, string $end): array
    {
        $achats = DB::connection('tenant')->table('factures_achats as fa')
            ->join('fournisseurs as fr', 'fr.id', '=', 'fa.fournisseur_id')
            ->where('fa.tenant_id', $this->tid())
            ->whereNull('fa.deleted_at')
            ->whereBetween('fa.date_facture', [$start, $end])
            ->select('fa.id', 'fa.numero', 'fa.date_facture', 'fr.societe', 'fa.total_ht', 'fa.total_tva', 'fa.total_ttc', DB::raw("'achat' as type"))
            ->get();

        $depenses = DB::connection('tenant')->table('depenses as d')
            ->where('d.tenant_id', $this->tid())
            ->whereNull('d.deleted_at')
            ->whereBetween('d.date_depense', [$start, $end])
            ->select('d.id', DB::raw("'' as numero"), 'd.date_depense as date_facture', 'd.libelle as societe', 'd.montant_ht as total_ht', 'd.montant_tva as total_tva', 'd.montant as total_ttc', DB::raw("'depense' as type"))
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
            ->sum('total_tva');

        $deductibleDepenses = DB::connection('tenant')->table('depenses')
            ->where('tenant_id', $this->tid())
            ->whereNull('deleted_at')
            ->whereBetween('date_depense', [$start, $end])
            ->sum('montant_tva');

        return [
            'collected_vat' => (float) $collected,
            'deductible_vat' => (float) ($deductibleAchats + $deductibleDepenses),
            'net_vat' => (float) ($collected - ($deductibleAchats + $deductibleDepenses))
        ];
    }

    /**
     * Valorisation du Stock
     */
    public function inventoryValuation(): array
    {
        return DB::connection('tenant')->table('produits')
            ->where('tenant_id', $this->tid())
            ->whereNull('deleted_at')
            ->where('is_service', 0)
            ->selectRaw('SUM(stock_actuel * prix_achat) as total_value_purchase, SUM(stock_actuel * prix_vente) as total_value_sale')
            ->first() ? (array) DB::connection('tenant')->table('produits')->where('tenant_id', $this->tid())->whereNull('deleted_at')->where('is_service', 0)->selectRaw('SUM(stock_actuel * prix_achat) as total_value_purchase, SUM(stock_actuel * prix_vente) as total_value_sale')->first() : ['total_value_purchase' => 0, 'total_value_sale' => 0];
    }
}
