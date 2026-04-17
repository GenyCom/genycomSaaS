<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard) {}

    public function kpis(): JsonResponse
    {
        return response()->json($this->dashboard->getKPIs());
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
}
