<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\IdEncoder;
use Symfony\Component\HttpFoundation\Response;

class DecodeIdsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Decode route parameters (e.g. {client}, {id}, etc.)
        $route = $request->route();
        if ($route) {
            foreach ($route->parameters() as $key => $value) {
                if (is_string($value) && IdEncoder::isUuid($value)) {
                    $decoded = IdEncoder::decode($value);
                    if ($decoded !== null) {
                        $route->setParameter($key, $decoded);
                    }
                }
            }
        }

        // Decode request payload (JSON, query, post body)
        $input = $request->all();
        $input = $this->decodeArrayIdentifiers($input);
        $request->replace($input);

        return $next($request);
    }

    /**
     * Recursively decode UUID strings back to integers in arrays.
     */
    protected function decodeArrayIdentifiers(array $array): array
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->decodeArrayIdentifiers($value);
            } elseif (is_string($value) && IdEncoder::isUuid($value)) {
                $decoded = IdEncoder::decode($value);
                if ($decoded !== null) {
                    $value = $decoded;
                }
            }
        }
        return $array;
    }
}
