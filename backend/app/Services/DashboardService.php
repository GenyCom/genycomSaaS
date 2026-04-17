<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Service du tableau de bord — calcul des KPIs.
 */
class DashboardService
{
    private int $tenantId;

    public function __construct()
    {
        $this->tenantId = auth()->user()->tenant_id ?? 0;
    }

    /**
     * Tous les KPIs principaux.
     */
    public function getKPIs(): array
    {
        return [
            'ca_mois'               => $this->caMois(),
            'ca_annee'              => $this->caAnnee(),
            'factures_impayees'     => $this->facturesImpayees(),
            'encours_clients'       => $this->encoursClients(),
            'encours_fournisseurs'  => $this->encoursFournisseurs(),
            'devis_en_cours'        => $this->devisEnCours(),
            'commandes_en_cours'    => $this->commandesEnCours(),
            'alertes_stock'         => $this->alertesStockCount(),
            'depenses_mois'         => $this->depensesMois(),
            'nb_clients'            => DB::table('clients')->where('tenant_id', $this->tenantId)->whereNull('deleted_at')->count(),
            'nb_produits'           => DB::table('produits')->where('tenant_id', $this->tenantId)->whereNull('deleted_at')->count(),
            'nb_fournisseurs'       => DB::table('fournisseurs')->where('tenant_id', $this->tenantId)->whereNull('deleted_at')->count(),
        ];
    }

    public function caMois(): float
    {
        return (float) DB::table('factures')
            ->where('tenant_id', $this->tenantId)
            ->whereNull('deleted_at')
            ->whereMonth('date_facture', now()->month)
            ->whereYear('date_facture', now()->year)
            ->sum('total_ttc');
    }

    public function caAnnee(): float
    {
        return (float) DB::table('factures')
            ->where('tenant_id', $this->tenantId)
            ->whereNull('deleted_at')
            ->whereYear('date_facture', now()->year)
            ->sum('total_ttc');
    }

    public function facturesImpayees(): array
    {
        $q = DB::table('factures')
            ->where('tenant_id', $this->tenantId)
            ->whereNull('deleted_at')
            ->where('est_reglee', 0)
            ->whereNotNull('numero');

        return [
            'count'   => $q->count(),
            'montant' => (float) $q->sum(DB::raw('total_ttc - montant_regle')),
        ];
    }

    public function encoursClients(): float
    {
        return (float) DB::table('factures')
            ->where('tenant_id', $this->tenantId)
            ->whereNull('deleted_at')
            ->where('est_reglee', 0)
            ->sum(DB::raw('total_ttc - montant_regle'));
    }

    public function encoursFournisseurs(): float
    {
        return (float) DB::table('dettes_fournisseur')
            ->where('tenant_id', $this->tenantId)
            ->whereNull('deleted_at')
            ->sum('montant_restant');
    }

    public function devisEnCours(): int
    {
        return DB::table('devis')
            ->where('tenant_id', $this->tenantId)
            ->whereNull('deleted_at')
            ->where('est_facture', 0)
            ->count();
    }

    public function commandesEnCours(): int
    {
        return DB::table('commandes')
            ->where('tenant_id', $this->tenantId)
            ->whereNull('deleted_at')
            ->where('est_livree', 0)
            ->count();
    }

    public function alertesStockCount(): int
    {
        return DB::table('alertes_stock')
            ->where('tenant_id', $this->tenantId)
            ->where('est_traitee', 0)
            ->count();
    }

    public function depensesMois(): float
    {
        return (float) DB::table('depenses')
            ->where('tenant_id', $this->tenantId)
            ->whereNull('deleted_at')
            ->whereMonth('date_depense', now()->month)
            ->whereYear('date_depense', now()->year)
            ->sum('montant');
    }

    /**
     * CA mensuel sur 12 mois pour graphique.
     */
    public function caMensuel(): array
    {
        return DB::table('factures')
            ->where('tenant_id', $this->tenantId)
            ->whereNull('deleted_at')
            ->whereRaw("date_facture >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)")
            ->selectRaw("DATE_FORMAT(date_facture, '%Y-%m') as mois, SUM(total_ttc) as ca, SUM(total_ht) as ca_ht")
            ->groupByRaw("DATE_FORMAT(date_facture, '%Y-%m')")
            ->orderBy('mois')
            ->get()
            ->toArray();
    }

    /**
     * Top 10 produits les plus vendus (année en cours).
     */
    public function topVentes(int $limit = 10): array
    {
        return DB::table('ligne_facture as lf')
            ->join('factures as f', 'f.id', '=', 'lf.facture_id')
            ->join('produits as p', 'p.id', '=', 'lf.produit_id')
            ->where('f.tenant_id', $this->tenantId)
            ->whereNull('f.deleted_at')
            ->whereNull('lf.deleted_at')
            ->whereYear('f.date_facture', now()->year)
            ->selectRaw('p.designation, p.reference, SUM(lf.quantite) as qte_vendue, SUM(lf.montant_ttc) as ca_ttc')
            ->groupBy('p.id', 'p.designation', 'p.reference')
            ->orderByDesc('ca_ttc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Top 10 clients par CA (année en cours).
     */
    public function topClients(int $limit = 10): array
    {
        return DB::table('factures as f')
            ->join('clients as c', 'c.id', '=', 'f.client_id')
            ->where('f.tenant_id', $this->tenantId)
            ->whereNull('f.deleted_at')
            ->whereYear('f.date_facture', now()->year)
            ->selectRaw('c.societe, c.code_client, COUNT(f.id) as nb_factures, SUM(f.total_ttc) as ca_total')
            ->groupBy('c.id', 'c.societe', 'c.code_client')
            ->orderByDesc('ca_total')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Échéances prochaines (7 jours).
     */
    public function echeancesProchaines(): array
    {
        $clients = DB::table('factures as f')
            ->join('clients as c', 'c.id', '=', 'f.client_id')
            ->where('f.tenant_id', $this->tenantId)
            ->where('f.est_reglee', 0)
            ->whereNull('f.deleted_at')
            ->whereNotNull('f.date_echeance')
            ->whereRaw('f.date_echeance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)')
            ->select('f.numero', 'c.societe', 'f.total_ttc', 'f.montant_regle', 'f.date_echeance',
                     DB::raw('(f.total_ttc - f.montant_regle) as reste_du'))
            ->orderBy('f.date_echeance')
            ->get();

        $fournisseurs = DB::table('dettes_fournisseur as d')
            ->join('fournisseurs as fr', 'fr.id', '=', 'd.fournisseur_id')
            ->where('d.tenant_id', $this->tenantId)
            ->whereNull('d.deleted_at')
            ->where('d.montant_restant', '>', 0)
            ->whereNotNull('d.date_echeance')
            ->whereRaw('d.date_echeance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)')
            ->select('d.numero', 'fr.societe', 'd.montant_restant', 'd.date_echeance')
            ->orderBy('d.date_echeance')
            ->get();

        return [
            'clients'       => $clients,
            'fournisseurs'  => $fournisseurs,
        ];
    }

    /**
     * Alertes de stock actives.
     */
    public function alertesStock(): array
    {
        return DB::table('alertes_stock as a')
            ->join('produits as p', 'p.id', '=', 'a.produit_id')
            ->where('a.tenant_id', $this->tenantId)
            ->where('a.est_traitee', 0)
            ->select('a.*', 'p.designation', 'p.reference', 'p.stock_actuel', 'p.seuil_alerte')
            ->orderByDesc('a.created_at')
            ->limit(50)
            ->get()
            ->toArray();
    }
}
