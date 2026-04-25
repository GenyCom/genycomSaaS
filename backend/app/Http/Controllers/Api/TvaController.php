<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TvaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // On récupère les taux depuis votre table existante
        $taux = DB::connection('tenant')
            ->table('taux_tva')
            ->where('actif', 1)
            ->orderBy('taux', 'desc')
            ->get();

        return response()->json([
            'data' => $taux
        ]);
    }
}