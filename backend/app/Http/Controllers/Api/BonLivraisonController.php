<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BonLivraison;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BonLivraisonController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BonLivraison::with([
                'client:id,societe,code_client',
                'etat',
                'bonCommande:id,numero',
                'devis:id,numero',
                'facture:id,numero',
            ])
            ->when($request->search, function($q, $v) {
                $q->where(function($sq) use ($v) {
                    $sq->where('numero', 'like', "%{$v}%")
                       ->orWhereHas('client', fn($cq) => $cq->where('societe', 'like', "%{$v}%"))
                       ->orWhereHas('bonCommande', fn($bq) => $bq->where('numero', 'like', "%{$v}%"))
                       ->orWhereHas('devis', fn($dq) => $dq->where('numero', 'like', "%{$v}%"))
                       ->orWhereHas('facture', fn($fq) => $fq->where('numero', 'like', "%{$v}%"));
                });
            })
            ->when($request->statut, fn($q, $v) => $q->where('statut', $v))
            ->orderBy('created_at', 'desc');
        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function show($id): JsonResponse
    {
        $bl = BonLivraison::with(['lignes.produit', 'client', 'devis', 'bonCommande', 'facture', 'etat', 'entrepot'])->findOrFail($id);
        return response()->json($bl);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'              => 'required|integer',
            'date_livraison'         => 'required|date',
            'entrepot_id'            => 'required|integer',
            'bon_commande_client_id' => 'nullable|integer',
            'devis_id'               => 'nullable|integer',
            'projet_id'              => 'nullable|integer',
            'observations'           => 'nullable|string',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.produit_id'    => 'nullable|integer',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.quantite_livree' => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        $tenantId = $request->get('current_tenant')->id;
        $userId   = auth()->id();

        return \DB::transaction(function () use ($data, $tenantId, $userId, $request) {
            $numero = app(\App\Services\NumerotationService::class)->generer($tenantId, 'BL');
            
            $bl = BonLivraison::create([
                'tenant_id'              => $tenantId,
                'numero'                 => $numero,
                'date_livraison'         => $data['date_livraison'],
                'client_id'              => $data['client_id'],
                'bon_commande_client_id' => $data['bon_commande_client_id'] ?? null,
                'devis_id'               => $data['devis_id'] ?? null,
                'projet_id'              => $data['projet_id'] ?? null,
                'entrepot_id'            => $data['entrepot_id'],
                'observations'           => $data['observations'] ?? null,
                'statut'                 => 'valide',
                'created_by'             => $userId
            ]);

            $totalHt = 0; $totalTva = 0;

            foreach ($data['lignes'] as $index => $ligne) {
                $pu = (float)$ligne['prix_unitaire'];
                $qty = (float)$ligne['quantite_livree'];
                $ht = $pu * $qty;
                $tva = $ht * 0.20; // Défaut 20% si direct

                \App\Models\LigneBonLivraison::create([
                    'tenant_id'        => $tenantId,
                    'bon_livraison_id' => $bl->id,
                    'produit_id'       => $ligne['produit_id'] ?? null,
                    'designation'      => $ligne['designation'],
                    'quantite_prevue'  => $qty,
                    'quantite_livree'  => $qty,
                    'prix_unitaire'    => $pu,
                    'taux_tva'         => 20,
                    'montant_ht'       => $ht,
                    'montant_tva'      => $tva,
                    'montant_ttc'      => $ht + $tva,
                    'ordre'            => $index + 1
                ]);

                $totalHt += $ht;
                $totalTva += $tva;

                if (!empty($ligne['produit_id'])) {
                    app(\App\Services\StockService::class)->enregistrerMouvement(
                        $ligne['produit_id'],
                        $qty,
                        'sortie_vente',
                        'BL',
                        $bl->id,
                        $userId,
                        $tenantId,
                        $data['entrepot_id']
                    );
                }
            }

            $bl->update([
                'total_ht' => $totalHt,
                'total_tva' => $totalTva,
                'total_ttc' => $totalHt + $totalTva
            ]);

            return response()->json(['id' => $bl->id, 'numero' => $numero, 'message' => 'Bon de livraison créé'], 201);
        });
    }

    public function destroy($id): JsonResponse
    {
        $bl = BonLivraison::findOrFail($id);
        if ($bl->facture_id) {
            return response()->json(['message' => 'Impossible de supprimer un bon de livraison déjà facturé.'], 422);
        }
        $bl->delete();
        return response()->json(['message' => 'Bon de livraison supprimé avec succès.']);
    }
}
