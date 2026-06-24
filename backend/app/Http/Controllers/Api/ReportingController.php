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
        $clientId = $request->get('client_id');
        
        return response()->json([
            'journal' => $this->reporting->salesJournal($start, $end, $clientId),
            'by_client' => $this->reporting->salesByClient($start, $end)
        ]);
    }

    public function purchases(Request $request): JsonResponse
    {
        $start = $request->get('start', now()->startOfMonth()->toDateString());
        $end = $request->get('end', now()->toDateString());
        $supplierId = $request->get('fournisseur_id');
        
        return response()->json($this->reporting->purchaseJournal($start, $end, $supplierId));
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

    public function payments(Request $request): JsonResponse
    {
        $start = $request->get('start', now()->startOfMonth()->toDateString());
        $end = $request->get('end', now()->toDateString());
        return response()->json($this->reporting->paymentsJournal($start, $end));
    }

    public function unpaid(): JsonResponse
    {
        return response()->json($this->reporting->unpaidInvoices());
    }

    public function cashFlow(Request $request): JsonResponse
    {
        $start = $request->get('start', now()->startOfMonth()->toDateString());
        $end = $request->get('end', now()->toDateString());
        return response()->json($this->reporting->cashAndProfitReport($start, $end));
    }

    public function cheques(Request $request): JsonResponse
    {
        $status = $request->get('status');
        return response()->json($this->reporting->chequeReport($status));
    }

    public function updateChequeStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'statut_cheque' => 'required|string|in:en_attente,encaisse,impaye'
        ]);

        $tid = $request->get('current_tenant')->id ?? auth()->user()->tenant_id ?? 1;

        $updated = \Illuminate\Support\Facades\DB::connection('tenant')->table('reglements')
            ->where('tenant_id', $tid)
            ->where('id', $id)
            ->update([
                'statut_cheque' => $data['statut_cheque'],
                'updated_at' => now()
            ]);

        if ($updated) {
            // Si le chèque n'est plus en attente, on marque ses alertes comme lues
            if (in_array($data['statut_cheque'], ['encaisse', 'impaye'])) {
                \Illuminate\Support\Facades\DB::connection('tenant')->table('notifications')
                    ->where(function($q) {
                        $q->where('type', 'like', '%Cheque%')
                          ->orWhere('data->type', 'like', '%cheque%');
                    })
                    ->where('data->cheque_id', $id)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
            return response()->json(['message' => 'Statut du chèque mis à jour avec succès.']);
        }

        return response()->json(['message' => 'Chèque introuvable.'], 404);
    }
}
