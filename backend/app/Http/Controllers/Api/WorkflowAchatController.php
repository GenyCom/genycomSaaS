<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BonCommandeFournisseur;
use App\Models\BR;
use App\Services\ReceptionService;
use App\Services\FactureAchatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkflowAchatController extends Controller
{
    public function __construct(
        private ReceptionService $receptionService,
        private FactureAchatService $factureAchatService
    ) {}

    /**
     * Transforme une Commande Fournisseur (BCF) en Bon de Réception (BR).
     */
    public function commandeToBR(Request $request, int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            
            // Trouver la commande via le bon modèle
            $commande = BonCommandeFournisseur::with('lignes')->findOrFail($id);

            // Si aucune quantité spécifique n'est envoyée, on réceptionne TOUT par défaut
            $quantitesRecues = $request->input('quantites', []);
            if (empty($quantitesRecues)) {
                foreach ($commande->lignes as $ligne) {
                    $quantitesRecues[$ligne->id] = $ligne->quantite;
                }
            }

            $br = $this->receptionService->receptionnerCommande($commande, $quantitesRecues, $userId, $request->input('entrepot_id'));

            return response()->json([
                'message' => 'Bon de réception généré avec succès',
                'id'      => $br->id,
                'numero'  => $br->numero,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Transforme un BR en Facture d'Achat (ou traite plusieurs BR).
     */
    public function brToFacture(Request $request, ?int $brId = null): JsonResponse
    {
        try {
            $userId = auth()->id();
            $brIds  = $request->input('br_ids', []);
            
            // Si on passe par l'URL ({br}), on l'ajoute à la liste
            if ($brId) {
                $brIds[] = $brId;
            }

            if (empty($brIds)) {
                return response()->json(['message' => 'Aucun BR sélectionné'], 422);
            }

            $facture = $this->factureAchatService->genererFactureDepuisBR($brIds, $userId);

            return response()->json([
                'message' => 'Facture d\'achat générée avec succès',
                'id'      => $facture->id,
                'numero'  => $facture->numero,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
