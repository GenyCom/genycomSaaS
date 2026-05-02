<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportingController extends Controller
{
    public function __construct(private ReportingService $reporting) {}

    public function sales(Request $request): JsonResponse
    {
        $start = $request->get('start', now()->startOfMonth()->toDateString());
        $end = $request->get('end', now()->toDateString());
        
        return response()->json([
            'journal' => $this->reporting->salesJournal($start, $end),
            'by_client' => $this->reporting->salesByClient($start, $end)
        ]);
    }

    public function purchases(Request $request): JsonResponse
    {
        $start = $request->get('start', now()->startOfMonth()->toDateString());
        $end = $request->get('end', now()->toDateString());
        
        return response()->json($this->reporting->purchaseJournal($start, $end));
    }

    public function finance(Request $request): JsonResponse
    {
        $start = $request->get('start', now()->startOfMonth()->toDateString());
        $end = $request->get('end', now()->toDateString());
        
        return response()->json([
            'vat' => $this->reporting->vatReport($start, $end),
            'profitability' => $this->reporting->profitabilityByProject($start, $end)
        ]);
    }

    public function stock(): JsonResponse
    {
        return response()->json($this->reporting->inventoryValuation());
    }
}
