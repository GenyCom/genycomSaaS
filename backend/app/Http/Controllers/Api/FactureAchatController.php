<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FactureAchat;
use App\Models\Devise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FactureAchatController extends Controller
{
    /**
     * Liste des factures d'achat.
     */
    public function index(Request $request): JsonResponse
    {
        // On utilise bien le modèle FactureAchat (qui pointe vers factures_achats)
        $query = FactureAchat::with(['fournisseur:id,societe'])
            ->when($request->fournisseur_id, fn($q, $v) => $q->where('fournisseur_id', $v))
            ->when($request->statut, fn($q, $v) => $q->where('statut', $v))
            ->when($request->search, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('numero', 'like', "%{$v}%")
                   ->orWhereHas('fournisseur', fn($cq) => $cq->where('societe', 'like', "%{$v}%"));
            }))
            ->orderBy($request->sort_by ?? 'date_facture', $request->sort_dir ?? 'desc');

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    /**
     * Détail d'une facture d'achat.
     */
    public function show($id): JsonResponse
    {
        $facture = FactureAchat::with(['fournisseur', 'lignes.produit', 'receptionNotes'])->findOrFail($id);
        return response()->json($facture);
    }

    /**
     * Création d'une facture d'achat (manuelle).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'date_facture'   => 'required|date',
            'lignes'         => 'required|array|min:1',
            'devise_id'      => 'nullable|integer',
            'taux_change_document' => 'nullable|numeric',
        ]);

        $tenantId = $request->get('current_tenant')->id;

        // Gestion Devise & Taux
        $deviseId = $validated['devise_id'] ?? null;
        $taux = $validated['taux_change_document'] ?? null;

        if (empty($deviseId)) {
            $devise = Devise::where('tenant_id', $tenantId)->where('is_principale', true)->first();
            $deviseId = $devise?->id;
            $taux = 1.0;
        } elseif (empty($taux)) {
            $devise = Devise::find($deviseId);
            $taux = $devise?->taux_change ?? 1.0;
        }

        $facture = FactureAchat::create([
            'tenant_id'      => $tenantId,
            'numero'         => $request->input('numero') ?? 'FA-TEMP',
            'fournisseur_id' => $validated['fournisseur_id'],
            'date_facture'   => $validated['date_facture'],
            'date_echeance'  => $request->input('date_echeance'),
            'devise_id'      => $deviseId,
            'taux_change_document' => $taux,
            'statut'         => $request->input('statut', 'brouillon'),
            'montant_paye'   => $request->input('montant_paye', 0),
            'created_by'     => auth()->id(),
        ]);

        foreach ($request->input('lignes') as $ligne) {
            $facture->lignes()->create($ligne);
        }

        // Calcule les sommes classiques (HT, TVA, TTC)
        $facture->recalculerTotaux();
        // Force la logique métier pour les paiements
        $this->syncPaiementStatus($facture);

        return response()->json($facture, 201);
    }

    /**
     * Mise à jour d'une facture d'achat.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $facture = FactureAchat::findOrFail($id);
        
        $facture->update($request->only([
            'date_facture',
            'date_echeance',
            'observations',
            'statut',
            'devise_id',
            'taux_change_document',
            'montant_paye'
        ]));

        if ($request->has('lignes')) {
            $facture->lignes()->delete();
            foreach ($request->input('lignes') as $ligne) {
                $facture->lignes()->create($ligne);
            }
        }

        // Calcule les sommes classiques (HT, TVA, TTC)
        $facture->recalculerTotaux();
        // Force la logique métier pour les paiements
        $this->syncPaiementStatus($facture);

        return response()->json($facture);
    }

    /**
     * Suppression d'une facture d'achat.
     */
    public function destroy($id): JsonResponse
    {
        $facture = FactureAchat::findOrFail($id);
        
        if ($facture->montant_paye > 0) {
             return response()->json([
                'message' => 'Impossible de supprimer cette facture d\'achat car elle a déjà fait l\'objet d\'un paiement.'
            ], 422);
        }

        $facture->delete();
        return response()->json(['message' => 'Facture d\'achat supprimée avec succès']);
    }

    /**
     * Logique métier centralisée pour synchroniser l'état et les paiements
     */
    private function syncPaiementStatus(FactureAchat $facture)
    {
        // 1. Si l'utilisateur a cliqué sur "Payé" depuis le frontend, on solde les chiffres
        if ($facture->statut === 'paye') {
            $facture->montant_paye = $facture->montant_ttc;
            $facture->reste_a_payer = 0;
        } 
        // 2. Si le reste à payer tombe mathématiquement à 0 (via un paiement partiel final)
        elseif ($facture->reste_a_payer <= 0 && $facture->montant_ttc > 0) {
            $facture->statut = 'paye';
            $facture->reste_a_payer = 0;
            $facture->montant_paye = $facture->montant_ttc;
        }
        // 3. Si la facture est repassée en "Valide" ou "Brouillon" alors qu'elle était soldée (Annulation)
        else {
            if ($facture->montant_paye >= $facture->montant_ttc && $facture->statut !== 'paye') {
                $facture->montant_paye = 0;
                $facture->reste_a_payer = $facture->montant_ttc;
            }
        }

        $facture->save();
    }
}