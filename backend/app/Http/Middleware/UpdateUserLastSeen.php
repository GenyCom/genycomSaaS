<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLastSeen
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $user = $request->user();

            if ($user) {
                $cacheKey = "user_last_seen_touch:{$user->id}";

                if (!Cache::has($cacheKey)) {
                    Cache::put($cacheKey, true, now()->addMinutes(1));

                    // Direct DB update to keep it ultra lightweight
                    DB::connection('central')
                        ->table('users')
                        ->where('id', $user->id)
                        ->update([
                            'last_seen_at' => now(),
                            'last_seen_ip' => $request->ip(),
                        ]);
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore middleware errors so user request is never broken
        }

        return $response;
    }
}
