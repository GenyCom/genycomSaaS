<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Artisan;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard) {}

    public function kpis(Request $request): JsonResponse
    {
        // Déclenche la vérification des dépenses et des stocks au chargement
        Artisan::call('depenses:check-reminders');
        Artisan::call('stock:check-alerts');

        // On passe maintenant le filtre de période envoyé par Vue.js
        return response()->json($this->dashboard->getKPIs($request->get('periode', 'Ce mois')));
    }

    public function caMensuel(): JsonResponse
    {
        return response()->json($this->dashboard->caMensuel());
    }

    public function topVentes(): JsonResponse
    {
        return response()->json($this->dashboard->topVentes());
    }

    public function topClients(): JsonResponse
    {
        return response()->json($this->dashboard->topClients());
    }

    public function echeances(): JsonResponse
    {
        return response()->json($this->dashboard->echeancesProchaines());
    }

    public function alertesStock(): JsonResponse
    {
        return response()->json($this->dashboard->alertesStock());
    }

    public function stockStats(): JsonResponse
    {
        return response()->json($this->dashboard->stockDistribution());
    }
}