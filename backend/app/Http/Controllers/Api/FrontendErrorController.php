<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SendExceptionMailJob;
use Illuminate\Support\Facades\Cache;

class FrontendErrorController extends Controller
{
    public function report(Request $request)
    {
        // 1. Vérifier si le monitoring est activé
        if (!config('genycom.monitoring.enabled', true)) {
            return response()->json(['status' => 'ignored']);
        }

        $data = $request->validate([
            'message'   => 'required|string',
            'stack'     => 'nullable|string',
            'url'       => 'nullable|string',
            'component' => 'nullable|string',
            'info'      => 'nullable|string',
        ]);

        // 2. Anti-spam / Rate limiting
        $signature = md5('frontend|' . $data['message'] . '|' . ($data['component'] ?? ''));
        $cacheKey  = "exception_dispatch:{$signature}";
        $cooldown  = (int) config('genycom.monitoring.cooldown_minutes', 15);

        if (Cache::has($cacheKey)) {
            Cache::increment("{$cacheKey}:count");
            return response()->json(['status' => 'rate_limited']);
        }

        Cache::put($cacheKey, true, now()->addMinutes($cooldown));

        // 3. Contexte (peut être nul si l'erreur arrive avant l'auth)
        $user = auth('sanctum')->user();
        $tenant = $request->get('current_tenant');

        // 4. Formater les données pour le Mailable existant
        $errorData = [
            'exception_class' => 'Vue.js Error — ' . ($data['component'] ?? 'App'),
            'code'            => 0,
            'severity'        => 'FRONTEND',
            'message'         => $data['message'],
            'file'            => $data['url'] ?? $request->header('referer') ?? $request->fullUrl(),
            'line'            => 0,
            'trace'           => $data['stack'] ?? 'Aucune trace disponible',

            'tenant_id'       => $tenant?->id ?? null,
            'tenant_name'     => $tenant?->nom ?? $tenant?->name ?? 'Plateforme Centrale',
            'tenant_db'       => $tenant?->db_name ?? null,

            'user_id'         => $user?->id ?? null,
            'user_name'       => $user?->name ?? 'Non authentifié',
            'user_email'      => $user?->email ?? null,

            'url'             => $data['url'] ?? $request->header('referer') ?? $request->fullUrl(),
            'method'          => 'FRONTEND_ACTION',
            'ip'              => $request->ip(),
            'user_agent'      => $request->userAgent(),
            'input'           => json_encode([
                'component' => $data['component'] ?? 'N/A',
                'info'      => $data['info'] ?? 'N/A'
            ], JSON_PRETTY_PRINT),

            'environment'     => app()->environment(),
            'php_version'     => 'Navigateur Client',
            'laravel_version' => 'Vue 3',
            'timestamp'       => now()->format('d/m/Y H:i:s'),
            'memory_usage'    => 'N/A',
        ];

        // 5. Dispatcher le même Job que pour le backend
        SendExceptionMailJob::dispatch($errorData)
            ->onQueue(config('genycom.monitoring.queue_name', 'monitoring'));

        return response()->json(['status' => 'reported']);
    }
}
