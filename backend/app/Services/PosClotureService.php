<?php

namespace App\Services;

use App\Models\Facture;
use App\Models\PosCloture;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PosClotureService
{
    /**
     * Ensure pos_clotures table exists in current tenant DB.
     */
    public function ensureTableExists(): void
    {
        try {
            if (!Schema::connection('tenant')->hasTable('pos_clotures')) {
                Schema::connection('tenant')->create('pos_clotures', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('tenant_id')->default(1)->index();
                    $table->string('numero', 50);
                    $table->date('date_cloture')->index();
                    $table->dateTime('opened_at');
                    $table->dateTime('closed_at');
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->string('nom_caissier', 150)->nullable();
                    $table->decimal('fond_initial', 15, 2)->default(0.00);
                    $table->decimal('total_ventes', 15, 2)->default(0.00);
                    $table->decimal('total_especes', 15, 2)->default(0.00);
                    $table->decimal('total_carte', 15, 2)->default(0.00);
                    $table->decimal('total_cheque', 15, 2)->default(0.00);
                    $table->decimal('total_virement', 15, 2)->default(0.00);
                    $table->decimal('total_autre', 15, 2)->default(0.00);
                    $table->decimal('total_attendu', 15, 2)->default(0.00);
                    $table->decimal('total_declare', 15, 2)->default(0.00);
                    $table->decimal('ecart', 15, 2)->default(0.00);
                    $table->string('statut_ecart', 30)->default('conforme');
                    $table->unsignedInteger('nb_ventes')->default(0);
                    $table->text('observations')->nullable();
                    $table->mediumText('rapport_texte')->nullable();
                    $table->timestamps();
                    $table->softDeletes();
                });
            }
        } catch (\Throwable $e) {
            \Log::warning('PosClotureService ensureTableExists: ' . $e->getMessage());
        }
    }

    /**
     * Get live summary of the current session before closing.
     */
    public function getCurrentSession(int $tenantId, ?User $user, ?float $fondInitial = null): array
    {
        $this->ensureTableExists();

        $now = Carbon::now();
        $today = Carbon::today();

        // Check if there was a previous closure today or recently
        $lastCloture = PosCloture::where('tenant_id', $tenantId)
            ->orderByDesc('id')
            ->first();

        // Determine opening time:
        // If last cloture was today, current session starts at last cloture's closed_at
        // Otherwise, starts at earliest sale today, or default 08:00
        if ($lastCloture && $lastCloture->closed_at->isToday()) {
            $openedAt = $lastCloture->closed_at;
        } else {
            // Find earliest sale today
            $firstSale = Facture::where('tenant_id', $tenantId)
                ->where(function ($q) {
                    $q->where('observations', 'like', '[POS]%')
                      ->orWhereHas('client', function ($cq) {
                          $cq->where('nom', 'like', '%Comptoir%');
                      });
                })
                ->whereDoesntHave('etat', function ($eq) {
                    $eq->where('code', 'ANN');
                })
                ->whereDate('created_at', $today)
                ->orderBy('created_at')
                ->first();

            if ($firstSale && $firstSale->created_at) {
                $openedAt = $firstSale->created_at;
            } else {
                $openedAt = $today->copy()->setTime(8, 0, 0);
            }
        }

        // Fetch sales in this session window
        $salesQuery = Facture::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('observations', 'like', '[POS]%')
                  ->orWhereHas('client', function ($cq) {
                      $cq->where('nom', 'like', '%Comptoir%');
                  });
            })
            ->whereDoesntHave('etat', function ($eq) {
                $eq->where('code', 'ANN');
            })
            ->where('created_at', '>=', $openedAt)
            ->with(['reglements.modeReglement']);

        $sales = $salesQuery->get();

        $totalVentes = 0.0;
        $totalEspeces = 0.0;
        $totalCarte = 0.0;
        $totalCheque = 0.0;
        $totalVirement = 0.0;
        $totalAutre = 0.0;

        foreach ($sales as $sale) {
            $montant = (float) $sale->total_ttc;
            $totalVentes += $montant;

            $reglement = $sale->reglements->first();
            $modeRaw = strtolower($reglement?->modeReglement?->libelle ?? '');
            if (!$modeRaw && $reglement?->observations) {
                $modeRaw = strtolower(str_replace('Paiement POS — ', '', $reglement->observations));
            }

            if (str_contains($modeRaw, 'esp') || str_contains($modeRaw, 'cash')) {
                $totalEspeces += $montant;
            } elseif (str_contains($modeRaw, 'cart') || str_contains($modeRaw, 'tpe') || str_contains($modeRaw, 'cb')) {
                $totalCarte += $montant;
            } elseif (str_contains($modeRaw, 'ch') || str_contains($modeRaw, 'chèque') || str_contains($modeRaw, 'cheque')) {
                $totalCheque += $montant;
            } elseif (str_contains($modeRaw, 'vir')) {
                $totalVirement += $montant;
            } else {
                // If undetermined, check sale observation or default to especes
                if (str_contains(strtolower($sale->observations ?? ''), 'carte')) {
                    $totalCarte += $montant;
                } else {
                    $totalEspeces += $montant;
                }
            }
        }

        $effectiveFondInitial = $fondInitial !== null ? (float) $fondInitial : (float) ($lastCloture?->fond_initial ?? 0.0);
        $totalAttendu = $effectiveFondInitial + $totalEspeces;

        $caissierNom = $user ? trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')) : 'Caissier';
        if (empty($caissierNom)) {
            $caissierNom = $user->email ?? 'Caissier Principal';
        }

        return [
            'date'              => $today->format('d/m/Y'),
            'date_raw'          => $today->toDateString(),
            'caissier'          => $caissierNom,
            'opened_at'         => $openedAt->format('H:i'),
            'opened_at_raw'     => $openedAt->toDateTimeString(),
            'closed_at'         => $now->format('H:i'),
            'closed_at_raw'     => $now->toDateTimeString(),
            'fond_initial'      => round($effectiveFondInitial, 2),
            'nb_ventes'         => $sales->count(),
            'total_ventes'      => round($totalVentes, 2),
            'total_especes'     => round($totalEspeces, 2),
            'total_carte'       => round($totalCarte, 2),
            'total_cheque'      => round($totalCheque, 2),
            'total_virement'    => round($totalVirement, 2),
            'total_autre'       => round($totalAutre, 2),
            'total_attendu'     => round($totalAttendu, 2),
            'detail_paiements'  => [
                ['mode' => 'Espèces', 'montant' => round($totalEspeces, 2)],
                ['mode' => 'Carte Bancaire', 'montant' => round($totalCarte, 2)],
                ['mode' => 'Chèque', 'montant' => round($totalCheque, 2)],
                ['mode' => 'Virement', 'montant' => round($totalVirement, 2)],
            ],
            'derniere_cloture'  => $lastCloture ? [
                'numero'        => $lastCloture->numero,
                'date'          => $lastCloture->date_cloture->format('d/m/Y'),
                'closed_at'     => $lastCloture->closed_at->format('d/m/Y H:i'),
                'total_ventes'  => (float) $lastCloture->total_ventes,
                'ecart'         => (float) $lastCloture->ecart,
                'statut_ecart'  => $lastCloture->statut_ecart,
            ] : null,
        ];
    }

    /**
     * Generate standard Z-Report ticket text exact format.
     */
    public function genererRapportZTexte(array $data): string
    {
        $date        = $data['date'] ?? Carbon::now()->format('d/m/Y');
        $caissier    = $data['caissier'] ?? 'Caissier';
        $ouverture   = $data['opened_at'] ?? '08:00';
        $fermeture   = $data['closed_at'] ?? Carbon::now()->format('H:i');
        $totalVentes = number_format((float) ($data['total_ventes'] ?? 0), 2, '.', '');
        $especes     = number_format((float) ($data['total_especes'] ?? 0), 2, '.', '');
        $carte       = number_format((float) ($data['total_carte'] ?? 0), 2, '.', '');
        $cheque      = (float) ($data['total_cheque'] ?? 0);
        $virement    = (float) ($data['total_virement'] ?? 0);

        $fondInitial = number_format((float) ($data['fond_initial'] ?? 0), 2, '.', '');
        $espAjoutees = $especes;
        $totalAttendu = number_format((float) ($data['total_attendu'] ?? 0), 2, '.', '');
        $totalDeclare = number_format((float) ($data['total_declare'] ?? 0), 2, '.', '');

        $ecartVal = (float) ($data['ecart'] ?? 0);
        $ecartStr = ($ecartVal > 0 ? '+' : '') . number_format($ecartVal, 2, '.', '');

        $statutEcart = match (true) {
            $ecartVal == 0 => '(Conforme)',
            $ecartVal < 0  => '(Manquant)',
            default        => '(Excédent)',
        };

        // Align numbers right with fixed padding
        $pad = fn($val) => str_pad($val, 10, ' ', STR_PAD_LEFT);

        $out = [];
        $out[] = "================================";
        $out[] = "      RAPPORT DE CLOTURE Z      ";
        $out[] = "================================";
        $out[] = "Date : {$date}";
        $out[] = "Caissier : {$caissier}";
        $out[] = "Ouverture : {$ouverture}";
        $out[] = "Fermeture : {$fermeture}";
        $out[] = "--------------------------------";
        $out[] = "1. RÉCAPITULATIF DES VENTES";
        $out[] = "   Total Ventes    : " . $pad($totalVentes);
        $out[] = "--------------------------------";
        $out[] = "2. DÉTAIL PAR PAIEMENT";
        $out[] = "   Espèces         : " . $pad($especes);
        $out[] = "   Carte Bancaire  : " . $pad($carte);
        if ($cheque > 0) {
            $out[] = "   Chèque          : " . $pad(number_format($cheque, 2, '.', ''));
        }
        if ($virement > 0) {
            $out[] = "   Virement        : " . $pad(number_format($virement, 2, '.', ''));
        }
        $out[] = "--------------------------------";
        $out[] = "3. CONTRÔLE TIROIR-CAISSE";
        $out[] = "   Fond initial    : " . $pad($fondInitial);
        $out[] = "   Espèces ajoutées: " . $pad($espAjoutees);
        $out[] = "   Total Attendu   : " . $pad($totalAttendu);
        $out[] = "   Total Déclaré   : " . $pad($totalDeclare);
        $out[] = "--------------------------------";
        $out[] = "   ECART           : " . $pad($ecartStr) . " {$statutEcart}";
        $out[] = "================================";

        return implode("\n", $out);
    }

    /**
     * Execute and persist a Cash Closing (Clôture Z).
     */
    public function cloturer(int $tenantId, ?User $user, array $payload): PosCloture
    {
        $this->ensureTableExists();

        $fondInitial  = (float) ($payload['fond_initial'] ?? 0.0);
        $totalDeclare = (float) ($payload['total_declare'] ?? 0.0);
        $observations = $payload['observations'] ?? null;

        // Fetch session data
        $session = $this->getCurrentSession($tenantId, $user, $fondInitial);

        $now = Carbon::now();
        $today = Carbon::today();
        $openedAt = Carbon::parse($session['opened_at_raw'] ?? $now);
        $closedAt = $now;

        $totalVentes  = (float) $session['total_ventes'];
        $totalEspeces = (float) $session['total_especes'];
        $totalCarte   = (float) $session['total_carte'];
        $totalCheque  = (float) $session['total_cheque'];
        $totalVirement= (float) $session['total_virement'];
        $totalAutre   = (float) $session['total_autre'];
        $nbVentes     = (int) $session['nb_ventes'];

        $totalAttendu = $fondInitial + $totalEspeces;
        $ecart = round($totalDeclare - $totalAttendu, 2);

        $statutEcart = match (true) {
            $ecart == 0.0  => 'conforme',
            $ecart < 0.0   => 'manquant',
            default        => 'excedent',
        };

        // Generate Z-number: Z-YYYYMMDD-COUNT
        $dailyCount = PosCloture::where('tenant_id', $tenantId)
            ->whereDate('date_cloture', $today)
            ->count() + 1;
        $numero = sprintf('Z-%s-%03d', $today->format('Ymd'), $dailyCount);

        // Build Rapport Z text
        $rapportData = array_merge($session, [
            'date'          => $today->format('d/m/Y'),
            'opened_at'     => $openedAt->format('H:i'),
            'closed_at'     => $closedAt->format('H:i'),
            'fond_initial'  => $fondInitial,
            'total_attendu' => $totalAttendu,
            'total_declare' => $totalDeclare,
            'ecart'         => $ecart,
            'statut_ecart'  => $statutEcart,
        ]);

        $rapportTexte = $this->genererRapportZTexte($rapportData);

        return DB::transaction(function () use (
            $tenantId, $numero, $today, $openedAt, $closedAt, $user, $session,
            $fondInitial, $totalVentes, $totalEspeces, $totalCarte, $totalCheque,
            $totalVirement, $totalAutre, $totalAttendu, $totalDeclare, $ecart,
            $statutEcart, $nbVentes, $observations, $rapportTexte
        ) {
            return PosCloture::create([
                'tenant_id'      => $tenantId,
                'numero'         => $numero,
                'date_cloture'   => $today->toDateString(),
                'opened_at'      => $openedAt,
                'closed_at'      => $closedAt,
                'user_id'        => $user?->id,
                'nom_caissier'   => $session['caissier'],
                'fond_initial'   => $fondInitial,
                'total_ventes'   => $totalVentes,
                'total_especes'  => $totalEspeces,
                'total_carte'    => $totalCarte,
                'total_cheque'   => $totalCheque,
                'total_virement' => $totalVirement,
                'total_autre'    => $totalAutre,
                'total_attendu'  => $totalAttendu,
                'total_declare'  => $totalDeclare,
                'ecart'          => $ecart,
                'statut_ecart'   => $statutEcart,
                'nb_ventes'      => $nbVentes,
                'observations'   => $observations,
                'rapport_texte'  => $rapportTexte,
            ]);
        });
    }

    /**
     * List all past closures.
     */
    public function getHistoriqueClotures(int $tenantId, int $limit = 50)
    {
        $this->ensureTableExists();

        return PosCloture::where('tenant_id', $tenantId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function ($c) {
                return [
                    'id'            => $c->id,
                    'numero'        => $c->numero,
                    'date'          => $c->date_cloture ? $c->date_cloture->format('d/m/Y') : '',
                    'ouverture'     => $c->opened_at ? $c->opened_at->format('H:i') : '',
                    'fermeture'     => $c->closed_at ? $c->closed_at->format('H:i') : '',
                    'caissier'      => $c->nom_caissier,
                    'fond_initial'  => (float) $c->fond_initial,
                    'total_ventes'  => (float) $c->total_ventes,
                    'total_especes' => (float) $c->total_especes,
                    'total_carte'   => (float) $c->total_carte,
                    'total_attendu' => (float) $c->total_attendu,
                    'total_declare' => (float) $c->total_declare,
                    'ecart'         => (float) $c->ecart,
                    'statut_ecart'  => $c->statut_ecart,
                    'nb_ventes'     => (int) $c->nb_ventes,
                    'rapport_texte' => $c->rapport_texte,
                    'created_at'    => $c->created_at ? $c->created_at->format('d/m/Y H:i') : '',
                ];
            });
    }
}
