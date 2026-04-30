<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contrat;
use App\Models\LigneContrat;
use App\Models\Devise;
use Illuminate\Support\Facades\DB;

class ContratController extends Controller
{
    protected $numerotation;

    public function __construct(\App\Services\NumerotationService $numerotation)
    {
        $this->numerotation = $numerotation;
    }

    public function index()
    {
        $contrats = Contrat::with('client')->latest()->get();
        return response()->json($contrats);
    }

    public function show($id)
    {
        $contrat = Contrat::with(['client', 'lignes'])->findOrFail($id);
        return response()->json($contrat);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|integer',
            'titre' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'frequence' => 'required|string',
            'devise_id' => 'nullable|integer',
            'taux_change_document' => 'nullable|numeric',
        ]);

        DB::connection('tenant')->beginTransaction();
        try {
            $tenantId = request()->get('current_tenant')?->id ?? 1;
            
            // Création de l'entête
            $contrat = Contrat::create([
                'numero' => $this->numerotation->generer($tenantId, 'CONTRAT'),
                'client_id' => $request->client_id,
                'titre' => $request->titre,
                'date_debut' => $request->date_debut,
                'prochaine_echeance' => $request->date_debut,
                'frequence' => $request->frequence,
                'statut' => 'ACTIF',
                'observations' => $request->observations,
                'total_ht' => $request->total_ht ?? 0,
                'total_tva' => $request->total_tva ?? 0,
                'total_ttc' => $request->total_ttc ?? 0,
                'devise_id' => $request->devise_id,
                'taux_change_document' => $request->taux_change_document ?? 1.0,
                'created_by' => auth()->id(),
            ]);

            // Snapshot du taux si non fourni
            if (empty($contrat->devise_id)) {
                $devise = Devise::where('tenant_id', $contrat->tenant_id)->where('is_principale', true)->first();
                $contrat->update(['devise_id' => $devise?->id, 'taux_change_document' => 1.0]);
            } elseif (empty($contrat->taux_change_document)) {
                $devise = Devise::find($contrat->devise_id);
                $contrat->update(['taux_change_document' => $devise?->taux_change ?? 1.0]);
            }

            // Enregistrement des lignes
            if ($request->has('lignes') && is_array($request->lignes)) {
                foreach ($request->lignes as $index => $ligneData) {
                    $contrat->lignes()->create([
                        'produit_id' => $ligneData['produit_id'] ?? null,
                        'designation' => $ligneData['designation'],
                        'description' => $ligneData['description'] ?? null,
                        'quantite' => $ligneData['quantite'] ?? 1,
                        'prix_unitaire' => $ligneData['prix_unitaire'],
                        'taux_tva' => $ligneData['taux_tva'] ?? 0,
                        'montant_ht' => $ligneData['montant_ht'] ?? 0,
                        'montant_tva' => $ligneData['montant_tva'] ?? 0,
                        'montant_ttc' => $ligneData['montant_ttc'] ?? 0,
                        'ordre' => $index,
                    ]);
                }
            }

            DB::connection('tenant')->commit();
            return response()->json($contrat->load('lignes'), 201);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json(['message' => 'Erreur lors de la création du contrat', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $contrat = Contrat::findOrFail($id);

        DB::connection('tenant')->beginTransaction();
        try {
            $contrat->update($request->only([
                'titre', 'date_debut', 'frequence', 'statut', 'observations',
                'total_ht', 'total_tva', 'total_ttc', 'devise_id', 'taux_change_document'
            ]));

            if ($request->has('statut') && $request->statut === 'ACTIF' && !$contrat->prochaine_echeance) {
                 $contrat->update(['prochaine_echeance' => now()->toDateString()]);
            }

            if ($request->has('lignes') && is_array($request->lignes)) {
                $contrat->lignes()->delete();
                foreach ($request->lignes as $index => $ligneData) {
                    $contrat->lignes()->create([
                        'produit_id' => $ligneData['produit_id'] ?? null,
                        'designation' => $ligneData['designation'],
                        'description' => $ligneData['description'] ?? null,
                        'quantite' => $ligneData['quantite'] ?? 1,
                        'prix_unitaire' => $ligneData['prix_unitaire'],
                        'taux_tva' => $ligneData['taux_tva'] ?? 0,
                        'montant_ht' => $ligneData['montant_ht'] ?? 0,
                        'montant_tva' => $ligneData['montant_tva'] ?? 0,
                        'montant_ttc' => $ligneData['montant_ttc'] ?? 0,
                        'ordre' => $index,
                    ]);
                }
            }

            DB::connection('tenant')->commit();
            return response()->json($contrat->fresh('lignes'));
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json(['message' => 'Erreur lors de la mise à jour', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $contrat = Contrat::findOrFail($id);
        $contrat->delete();
        return response()->json(['message' => 'Contrat supprimé avec succès']);
    }
}
