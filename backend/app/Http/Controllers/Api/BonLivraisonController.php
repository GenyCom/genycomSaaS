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
        $bl = BonLivraison::with(['lignes.produit', 'client', 'devis', 'bonCommande', 'facture', 'etat'])->findOrFail($id);
        return response()->json($bl);
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
