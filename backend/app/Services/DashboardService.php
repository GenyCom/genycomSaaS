<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Service du tableau de bord — Version "GenyCom" Temps Réel & Sécurisée
 */
class DashboardService
{
    /**
     * Récupère le tenant_id de manière sécurisée
     */
    private function tid(): int
    {
        return request()->get('current_tenant')->id ?? auth()->user()->tenant_id ?? 1;
    }

    public function getKPIs(string $periode = 'Ce mois'): array
    {
        $startDate = $this->getStartDate($periode);
        $previousDates = $this->getPreviousPeriodDates($periode);
        
        $currentCA = $this->caPeriode($startDate);
        $previousCA = $this->caPeriodeEntre($previousDates['start'], $previousDates['end']);

        $beneficeNet = $this->beneficeNetPeriode($startDate);
        $beneficeMargePct = $this->beneficeMargePct($beneficeNet, $startDate);

        return [
            'ca_mois'               => $currentCA, 
            'ca_trend'              => $this->calculateTrend($currentCA, $previousCA),
            'ca_encaisse'           => $this->caEncaissePeriode($startDate),
            'ca_annee'              => $this->caAnnee(),
            'benefice_net'          => $beneficeNet,
            'benefice_marge_pct'    => $beneficeMargePct,
            'factures_impayees'     => $this->facturesImpayees(),
            'impayes_fournisseurs'  => $this->facturesAchatsImpayees(),
            'encours_clients'       => $this->encoursClients(),
            'encours_fournisseurs'  => $this->encoursFournisseurs(),
            'devis_en_cours'        => $this->devisEnCours(),
            'commandes_en_cours'    => $this->commandesEnCours(),
            'alertes_globales'      => $this->alertesStockCount(),
            'depenses_mois'         => $this->depensesPeriode($startDate),
            
            'nb_clients'            => $this->countTable('clients'),
            'nb_produits'           => $this->countTable('produits'),
            'nb_fournisseurs'       => $this->countTable('fournisseurs'),
        ];
    }

    private function getPreviousPeriodDates(string $periode): array
    {
        $now = now();
        return match ($periode) {
            'Trimestre' => [
                'start' => $now->copy()->subQuarter()->startOfQuarter(),
                'end'   => $now->copy()->subQuarter()->endOfQuarter(),
            ],
            'Année' => [
                'start' => $now->copy()->subYear()->startOfYear(),
                'end'   => $now->copy()->subYear()->endOfYear(),
            ],
            default => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end'   => $now->copy()->subMonth()->endOfMonth(),
            ],
        };
    }

    private function calculateTrend(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function countTable(string $table): int
    {
        try {
            return DB::connection('tenant')->table($table)->where('tenant_id', $this->tid())->whereNull('deleted_at')->count();
        } catch (\Exception $e) { return 0; }
    }

    public function facturesImpayees(): array
    {
        try {
            $tid = $this->tid();

            // Récupérer les IDs des états "Annulée" pour les exclure
            $etatsAnnules = DB::connection('tenant')->table('etat_document')
                ->where('tenant_id', $tid)
                ->where('type_document', 'facture')
                ->where('code', 'ANN')
                ->pluck('id');

            $q = DB::connection('tenant')->table('factures')
                ->where('tenant_id', $tid)
                ->whereNull('deleted_at')
                ->where(function ($sub) {
                    $sub->where('est_reglee', 0)
                        ->orWhereNull('est_reglee');
                })
                ->whereRaw('(total_ttc - montant_regle) > 0')
                ->whereNotNull('numero');

            if ($etatsAnnules->isNotEmpty()) {
                $q->where(function ($sub) use ($etatsAnnules) {
                    $sub->whereNull('etat_id')
                        ->orWhereNotIn('etat_id', $etatsAnnules);
                });
            }

            $count = (clone $q)->count();
            $montantFactures = (float) (clone $q)->sum(DB::raw('total_ttc - montant_regle'));

            $soldeInitial = (float) DB::connection('tenant')->table('clients')
                ->where('tenant_id', $tid)
                ->whereNull('deleted_at')
                ->sum('solde_initial');

            return [
                'count'   => $count,
                'montant' => $montantFactures + $soldeInitial,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Dashboard facturesImpayees error: ' . $e->getMessage());
            return ['count' => 0, 'montant' => 0];
        }
    }

    public function encoursClients(): float
    {
        try {
            $tid = $this->tid();

            // Récupérer les IDs des états "Annulée" pour les exclure
            $etatsAnnules = DB::connection('tenant')->table('etat_document')
                ->where('tenant_id', $tid)
                ->where('type_document', 'facture')
                ->where('code', 'ANN')
                ->pluck('id');

            $q = DB::connection('tenant')->table('factures')
                ->where('tenant_id', $tid)
                ->whereNull('deleted_at')
                ->where(function ($sub) {
                    $sub->where('est_reglee', 0)
                        ->orWhereNull('est_reglee');
                })
                ->whereRaw('(total_ttc - montant_regle) > 0');

            if ($etatsAnnules->isNotEmpty()) {
                $q->where(function ($sub) use ($etatsAnnules) {
                    $sub->whereNull('etat_id')
                        ->orWhereNotIn('etat_id', $etatsAnnules);
                });
            }

            $facturesEncours = (float) $q->sum(DB::raw('total_ttc - montant_regle'));

            $soldeInitial = (float) DB::connection('tenant')->table('clients')
                ->where('tenant_id', $tid)
                ->whereNull('deleted_at')
                ->sum('solde_initial');

            return $facturesEncours + $soldeInitial;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Dashboard encoursClients error: ' . $e->getMessage());
            return 0;
        }
    }

    public function encoursFournisseurs(): float
    {
        try {
            $tid = $this->tid();

            $etatsAnnules = DB::connection('tenant')->table('etat_document')
                ->where('tenant_id', $tid)
                ->whereIn('type_document', ['facture_achat', 'facture'])
                ->whereIn('code', ['ANN', 'ANL'])
                ->pluck('id');

            $q = DB::connection('tenant')->table('factures_achats')
                ->where('tenant_id', $tid)
                ->whereNull('deleted_at')
                ->where(function ($sub) {
                    $sub->whereNull('statut')
                        ->orWhere('statut', '!=', 'annule');
                })
                ->where(function ($sub) {
                    $sub->where('reste_a_payer', '>', 0)
                        ->orWhereRaw('(montant_ttc - COALESCE(montant_paye, 0)) > 0');
                });

            if ($etatsAnnules->isNotEmpty()) {
                $q->where(function ($sub) use ($etatsAnnules) {
                    $sub->whereNull('etat_id')
                        ->orWhereNotIn('etat_id', $etatsAnnules);
                });
            }

            return (float) $q->sum(DB::raw('CASE WHEN reste_a_payer IS NOT NULL AND reste_a_payer > 0 THEN reste_a_payer ELSE (montant_ttc - COALESCE(montant_paye, 0)) END'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Dashboard encoursFournisseurs error: ' . $e->getMessage());
            return 0;
        }
    }

    public function facturesAchatsImpayees(): array
    {
        try {
            $tid = $this->tid();

            $etatsAnnules = DB::connection('tenant')->table('etat_document')
                ->where('tenant_id', $tid)
                ->whereIn('type_document', ['facture_achat', 'facture'])
                ->whereIn('code', ['ANN', 'ANL'])
                ->pluck('id');

            $q = DB::connection('tenant')->table('factures_achats')
                ->where('tenant_id', $tid)
                ->whereNull('deleted_at')
                ->where(function ($sub) {
                    $sub->whereNull('statut')
                        ->orWhere('statut', '!=', 'annule');
                })
                ->where(function ($sub) {
                    $sub->where('reste_a_payer', '>', 0)
                        ->orWhereRaw('(montant_ttc - COALESCE(montant_paye, 0)) > 0');
                });

            if ($etatsAnnules->isNotEmpty()) {
                $q->where(function ($sub) use ($etatsAnnules) {
                    $sub->whereNull('etat_id')
                        ->orWhereNotIn('etat_id', $etatsAnnules);
                });
            }

            $count = (clone $q)->count();
            $montant = (float) (clone $q)->sum(DB::raw('CASE WHEN reste_a_payer IS NOT NULL AND reste_a_payer > 0 THEN reste_a_payer ELSE (montant_ttc - COALESCE(montant_paye, 0)) END'));

            return [
                'count'   => $count,
                'montant' => $montant,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Dashboard facturesAchatsImpayees error: ' . $e->getMessage());
            return ['count' => 0, 'montant' => 0.0];
        }
    }

    public function devisEnCours(): int
    {
        try {
            return DB::connection('tenant')->table('devis')
                ->where('tenant_id', $this->tid())
                ->whereNull('deleted_at')
                ->where('est_facture', 0)
                ->count();
        } catch (\Exception $e) { return 0; }
    }

    public function commandesEnCours(): int
    {
        try {
            return DB::connection('tenant')->table('commandes')
                ->where('tenant_id', $this->tid())
                ->whereNull('deleted_at')
                ->where('est_livree', 0)
                ->count();
        } catch (\Exception $e) { return 0; }
    }

    public function alertesStockCount(): int
    {
        $tid = $this->tid();
        $stock = 0;
        $factures = 0;

        // 1. Comptage des alertes stock (Lecture en direct de la table produits)
        try {
            $stock = DB::connection('tenant')->table('produits')
                ->where('tenant_id', $tid)
                ->where('is_service', 0)
                ->whereRaw('stock_actuel <= seuil_alerte')
                ->whereNull('deleted_at')
                ->count();
        } catch (\Exception $e) {
            // Ignoré silencieusement si problème de structure
        }

        // 2. Comptage des factures en retard
        try {
            $factures = DB::connection('tenant')->table('factures')
                ->where('tenant_id', $tid)
                ->where(function ($query) {
                    $query->where('est_reglee', 0)
                          ->orWhereNull('est_reglee');
                })
                ->whereNotNull('date_echeance')
                ->whereDate('date_echeance', '<=', now()->format('Y-m-d'))
                ->whereNull('deleted_at')
                ->count();
        } catch (\Exception $e) {
            // Ignoré silencieusement
        }

        return $stock + $factures;
    }

    public function caPeriodeEntre($start, $end): float
    {
        try {
            return (float) DB::connection('tenant')->table('factures')
                ->where('tenant_id', $this->tid())
                ->whereNull('deleted_at')
                ->whereBetween('date_facture', [$start, $end])
                ->sum('total_ttc');
        } catch (\Exception $e) { return 0; }
    }

    public function caPeriode($startDate): float
    {
        try {
            return (float) DB::connection('tenant')->table('factures')
                ->where('tenant_id', $this->tid())
                ->whereNull('deleted_at')
                ->where('date_facture', '>=', $startDate)
                ->sum('total_ttc');
        } catch (\Exception $e) { return 0; }
    }

    public function caAnnee(): float
    {
        try {
            return (float) DB::connection('tenant')->table('factures')
                ->where('tenant_id', $this->tid())
                ->whereNull('deleted_at')
                ->whereYear('date_facture', now()->year)
                ->sum('total_ttc');
        } catch (\Exception $e) { return 0; }
    }

    public function depensesPeriode($startDate): float
    {
        try {
            return (float) DB::connection('tenant')->table('depenses')
                ->where('tenant_id', $this->tid())
                ->whereNull('deleted_at')
                ->where('date_depense', '>=', $startDate)
                ->sum('montant');
        } catch (\Exception $e) { return 0; }
    }

    public function caEncaissePeriode($startDate): float
    {
        try {
            return (float) DB::connection('tenant')->table('reglements')
                ->where('tenant_id', $this->tid())
                ->where('payable_type', 'App\\Models\\Facture') 
                ->where('date_reglement', '>=', $startDate)
                ->sum('montant');
        } catch (\Exception $e) { return 0; }
    }

    public function beneficeNetPeriode($startDate): float
    {
        try {
            $tid = $this->tid();
            $depenses = (float) DB::connection('tenant')->table('depenses')
                ->where('tenant_id', $tid)
                ->whereNull('deleted_at')
                ->where('date_depense', '>=', $startDate)
                ->sum('montant');

            $lignes = DB::connection('tenant')->table('ligne_facture as lf')
                ->join('factures as f', 'f.id', '=', 'lf.facture_id')
                ->leftJoin('produits as p', 'p.id', '=', 'lf.produit_id')
                ->where('f.tenant_id', $tid)
                ->whereNull('f.deleted_at')
                ->whereNotNull('f.numero')
                ->where('f.date_facture', '>=', $startDate)
                ->select('lf.montant_ht', 'lf.quantite', 'p.prix_revient', 'p.is_service')
                ->get();

            $caHt = 0;
            $cogs = 0;

            foreach ($lignes as $l) {
                $caHt += (float) $l->montant_ht;
                if ($l->prix_revient && !$l->is_service) {
                    $cogs += (float) $l->quantite * (float) $l->prix_revient;
                }
            }

            // Fallback si pas de lignes détaillées avec total HT direct sur la facture
            if ($caHt == 0) {
                $caHt = (float) DB::connection('tenant')->table('factures')
                    ->where('tenant_id', $tid)
                    ->whereNull('deleted_at')
                    ->whereNotNull('numero')
                    ->where('date_facture', '>=', $startDate)
                    ->sum('total_ht');
            }

            // Fallback pour le coût d'achat si non renseigné sur les fiches produits
            if ($cogs == 0) {
                try {
                    $cogs = (float) DB::connection('tenant')->table('factures_achats')
                        ->where('tenant_id', $tid)
                        ->whereNull('deleted_at')
                        ->where('date_facture', '>=', $startDate)
                        ->sum('total_ht');
                } catch (\Exception $e) {}
            }

            $margeBrute = $caHt - $cogs;
            return round($margeBrute - $depenses, 2);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    public function beneficeMargePct(float $beneficeNet, $startDate): float
    {
        try {
            $tid = $this->tid();
            $caHt = (float) DB::connection('tenant')->table('factures')
                ->where('tenant_id', $tid)
                ->whereNull('deleted_at')
                ->whereNotNull('numero')
                ->where('date_facture', '>=', $startDate)
                ->sum('total_ht');

            if ($caHt <= 0) {
                $caHt = (float) DB::connection('tenant')->table('factures')
                    ->where('tenant_id', $tid)
                    ->whereNull('deleted_at')
                    ->whereNotNull('numero')
                    ->where('date_facture', '>=', $startDate)
                    ->sum('total_ttc');
            }

            if ($caHt > 0) {
                return round(($beneficeNet / $caHt) * 100, 1);
            }
            return 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    public function caMensuel(): array
    {
        try {
            return DB::connection('tenant')->table('factures')
                ->where('tenant_id', $this->tid())
                ->whereNull('deleted_at')
                ->whereNotNull('numero') // On ne prend que les factures validées
                ->whereRaw("date_facture >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)")
                ->selectRaw("DATE_FORMAT(date_facture, '%Y-%m') as mois, SUM(total_ttc) as ca")
                ->groupByRaw("DATE_FORMAT(date_facture, '%Y-%m')")
                ->orderBy('mois')
                ->get()
                ->toArray();
        } catch (\Exception $e) { return []; }
    }

    public function topVentes(string $periode = 'Ce mois', int $limit = 10): array
    {
        $startDate = $this->getStartDate($periode);
        try {
            return DB::connection('tenant')->table('ligne_facture as lf')
                ->join('factures as f', 'f.id', '=', 'lf.facture_id')
                ->join('produits as p', 'p.id', '=', 'lf.produit_id')
                ->where('f.tenant_id', $this->tid())
                ->whereNull('f.deleted_at')
                ->whereNull('lf.deleted_at')
                ->where('f.date_facture', '>=', $startDate)
                ->selectRaw('p.designation, p.reference, SUM(lf.quantite) as qte_vendue, SUM(lf.montant_ttc) as ca_ttc')
                ->groupBy('p.id', 'p.designation', 'p.reference')
                ->orderByDesc('qte_vendue')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) { return []; }
    }

    public function topClients(string $periode = 'Ce mois', int $limit = 10): array
    {
        $startDate = $this->getStartDate($periode);
        try {
            return DB::connection('tenant')->table('factures as f')
                ->join('clients as c', 'c.id', '=', 'f.client_id')
                ->where('f.tenant_id', $this->tid())
                ->whereNull('f.deleted_at')
                ->where('f.date_facture', '>=', $startDate)
                ->selectRaw('c.societe, c.code_client, COUNT(f.id) as nb_factures, SUM(f.total_ttc) as ca_total')
                ->groupBy('c.id', 'c.societe', 'c.code_client')
                ->orderByDesc('ca_total')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) { return []; }
    }

    private function getStartDate(string $periode): \Carbon\Carbon
    {
        return match ($periode) {
            'Trimestre' => now()->startOfQuarter(),
            'Année'     => now()->startOfYear(),
            default     => now()->startOfMonth(),
        };
    }

    public function echeancesProchaines(): array
    {
        try {
            $tid = $this->tid();
            $etatsAnnules = DB::connection('tenant')->table('etat_document')
                ->where('tenant_id', $tid)
                ->where('type_document', 'facture')
                ->where('code', 'ANN')
                ->pluck('id');

            $clientsQuery = DB::connection('tenant')->table('factures as f')
                ->join('clients as c', 'c.id', '=', 'f.client_id')
                ->where('f.tenant_id', $tid)
                ->where(function ($sub) {
                    $sub->where('f.est_reglee', 0)
                        ->orWhereNull('f.est_reglee');
                })
                ->whereRaw('(f.total_ttc - f.montant_regle) > 0')
                ->whereNull('f.deleted_at')
                ->whereNotNull('f.date_echeance')
                ->whereRaw('f.date_echeance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)');

            if ($etatsAnnules->isNotEmpty()) {
                $clientsQuery->where(function ($sub) use ($etatsAnnules) {
                    $sub->whereNull('f.etat_id')
                        ->orWhereNotIn('f.etat_id', $etatsAnnules);
                });
            }

            $clients = $clientsQuery
                ->select('f.numero', 'c.societe', 'f.total_ttc', 'f.montant_regle', 'f.date_echeance',
                         DB::raw('(f.total_ttc - f.montant_regle) as reste_du'))
                ->orderBy('f.date_echeance')
                ->get();

            $fournisseurs = [];
            try {
                $etatsAnnulesFourn = DB::connection('tenant')->table('etat_document')
                    ->where('tenant_id', $tid)
                    ->whereIn('type_document', ['facture_achat', 'facture'])
                    ->whereIn('code', ['ANN', 'ANL'])
                    ->pluck('id');

                $fournQuery = DB::connection('tenant')->table('factures_achats as d')
                    ->join('fournisseurs as fr', 'fr.id', '=', 'd.fournisseur_id')
                    ->where('d.tenant_id', $tid)
                    ->whereNull('d.deleted_at')
                    ->where(function ($sub) {
                        $sub->whereNull('d.statut')
                            ->orWhere('d.statut', '!=', 'annule');
                    })
                    ->where('d.reste_a_payer', '>', 0)
                    ->whereNotNull('d.date_echeance')
                    ->whereRaw('d.date_echeance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)');

                if ($etatsAnnulesFourn->isNotEmpty()) {
                    $fournQuery->where(function ($sub) use ($etatsAnnulesFourn) {
                        $sub->whereNull('d.etat_id')
                            ->orWhereNotIn('d.etat_id', $etatsAnnulesFourn);
                    });
                }

                $fournisseurs = $fournQuery
                    ->select('d.numero', 'fr.societe', 'd.reste_a_payer as montant_restant', 'd.date_echeance')
                    ->orderBy('d.date_echeance')
                    ->get();
            } catch (\Exception $e) {
                // Table factures_achats non existante, on ignore
            }

            return [
                'clients'       => $clients,
                'fournisseurs'  => $fournisseurs,
            ];
        } catch (\Exception $e) {
            return ['clients' => [], 'fournisseurs' => []];
        }
    }

    public function alertesStock(): array
    {
        $tid = $this->tid();
        $stockAlerts = [];
        $overdueFactures = [];

        // 1. Récupération des alertes stock (Temps réel depuis les produits)
        try {
            $stockAlerts = DB::connection('tenant')->table('produits')
                ->where('tenant_id', $tid)
                ->where('is_service', 0)
                ->whereRaw('stock_actuel <= seuil_alerte')
                ->whereNull('deleted_at')
                ->select('id as produit_id', 'designation', 'reference', 'stock_actuel', 'seuil_alerte', 'updated_at')
                ->get()
                ->map(function($p) {
                    return [
                        'id' => 'stk-' . $p->produit_id,
                        'type_alerte' => 'low_stock',
                        'type_alerte_label' => 'STOCK',
                        'message' => "Stock critique : {$p->reference}",
                        'message_complet' => "Le produit {$p->designation} a atteint son seuil d'alerte ({$p->stock_actuel} en stock pour un seuil de {$p->seuil_alerte}).",
                        'created_at' => $p->updated_at ?? now(),
                        'document_id' => $p->produit_id,
                        'document_type' => 'produit'
                    ];
                })
                ->toArray();
        } catch (\Exception $e) { }

        // 2. Récupération des factures en retard
        try {
            $etatsAnnules = DB::connection('tenant')->table('etat_document')
                ->where('tenant_id', $tid)
                ->where('type_document', 'facture')
                ->where('code', 'ANN')
                ->pluck('id');

            $overdueQuery = DB::connection('tenant')->table('factures')
                ->where('tenant_id', $tid)
                ->where(function ($query) {
                    $query->where('est_reglee', 0)
                          ->orWhereNull('est_reglee');
                })
                ->whereRaw('(total_ttc - montant_regle) > 0')
                ->whereNotNull('date_echeance')
                ->whereDate('date_echeance', '<=', now()->format('Y-m-d'))
                ->whereNull('deleted_at');

            if ($etatsAnnules->isNotEmpty()) {
                $overdueQuery->where(function ($sub) use ($etatsAnnules) {
                    $sub->whereNull('etat_id')
                        ->orWhereNotIn('etat_id', $etatsAnnules);
                });
            }

            $overdueFactures = $overdueQuery
                ->get()
                ->map(function($f) {
                    return [
                        'id' => 'fac-' . $f->id,
                        'type_alerte' => 'overdue_invoice',
                        'type_alerte_label' => 'PAIEMENT',
                        'message' => "La facture {$f->numero} est en retard",
                        'message_complet' => "La facture {$f->numero} (échéance au {$f->date_echeance}) n'est toujours pas réglée.",
                        'created_at' => $f->created_at,
                        'document_id' => $f->id,
                        'document_type' => 'facture'
                    ];
                })
                ->toArray();
        } catch (\Exception $e) { }

        // Fusionner et trier du plus récent au plus ancien
        $merged = array_merge($stockAlerts, $overdueFactures);
        
        usort($merged, function($a, $b) {
            return strtotime($b['created_at'] ?? $b->created_at) <=> strtotime($a['created_at'] ?? $a->created_at);
        });

        return array_slice($merged, 0, 50);
    }

    public function stockDistribution(): array
    {
        try {
            $tid = $this->tid();
            
            $total = DB::connection('tenant')->table('produits')
                ->where('tenant_id', $tid)
                ->whereNull('deleted_at')
                ->where('is_service', 0)
                ->count();

            if ($total === 0) return ['sufficient' => 0, 'critical' => 0, 'rupture' => 0, 'total' => 0];

            $rupture = DB::connection('tenant')->table('produits')
                ->where('tenant_id', $tid)
                ->whereNull('deleted_at')
                ->where('is_service', 0)
                ->where('stock_actuel', '<=', 0)
                ->count();

            $critical = DB::connection('tenant')->table('produits')
                ->where('tenant_id', $tid)
                ->whereNull('deleted_at')
                ->where('is_service', 0)
                ->where('stock_actuel', '>', 0)
                ->whereRaw('stock_actuel <= seuil_alerte')
                ->count();

            $sufficient = $total - $rupture - $critical;

            return [
                'sufficient' => $sufficient,
                'critical'   => $critical,
                'rupture'    => $rupture,
                'total'      => $total,
                'percent_sufficient' => round(($sufficient / $total) * 100),
                'percent_critical'   => round(($critical / $total) * 100),
                'percent_rupture'    => round(($rupture / $total) * 100),
            ];
        } catch (\Exception $e) { 
            return ['sufficient' => 0, 'critical' => 0, 'rupture' => 0, 'total' => 0]; 
        }
    }
}