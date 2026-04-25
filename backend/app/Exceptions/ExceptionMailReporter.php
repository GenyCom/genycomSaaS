<?php

namespace App\Exceptions;

use App\Jobs\SendExceptionMailJob;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service centralisé de reporting d'exceptions par email.
 *
 * Responsabilités :
 *  - Collecte du contexte complet (tenant, user, request, trace)
 *  - Filtrage des exceptions non pertinentes (404, CSRF, validation, auth)
 *  - Dispatch asynchrone via SendExceptionMailJob
 *  - Pré-filtrage rate limit côté dispatch (évite de surcharger la queue)
 */
class ExceptionMailReporter
{
    /**
     * Exceptions à ignorer (ne génèrent pas de rapport).
     */
    private const IGNORED_EXCEPTIONS = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
        \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
    ];

    /**
     * Codes HTTP à ignorer (erreurs client, pas serveur).
     */
    private const IGNORED_HTTP_CODES = [400, 401, 403, 404, 405, 419, 422, 429];

    /**
     * Traite une exception et dispatch le job d'envoi si pertinent.
     */
    public static function report(Throwable $e): void
    {
        try {
            // 1. Vérifier si le monitoring est activé
            if (!config('genycom.monitoring.enabled', true)) {
                return;
            }

            // 2. Filtrage des exceptions non pertinentes
            if (static::shouldIgnore($e)) {
                return;
            }

            // 3. Pré-filtrage rate limit côté dispatch
            // (le job lui-même refait la vérification, mais ceci évite de saturer la queue)
            $signature = md5(get_class($e) . '|' . $e->getMessage() . '|' . $e->getFile() . '|' . $e->getLine());
            $cacheKey  = "exception_dispatch:{$signature}";
            $cooldown  = (int) config('genycom.monitoring.cooldown_minutes', 15);

            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                \Illuminate\Support\Facades\Cache::increment("{$cacheKey}:count");
                return;
            }

            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addMinutes($cooldown));

            // 4. Collecter le contexte complet
            $errorData = static::buildErrorData($e);

            // 5. Dispatcher le job sur la queue
            SendExceptionMailJob::dispatch($errorData)
                ->onQueue(config('genycom.monitoring.queue_name', 'monitoring'));

        } catch (\Throwable $reportingError) {
            // Ne JAMAIS laisser le reporting casser l'application
            Log::channel('daily')->error('[ExceptionMailReporter] Échec du reporting', [
                'reporter_error'  => $reportingError->getMessage(),
                'original_error'  => $e->getMessage(),
            ]);
        }
    }

    /**
     * Détermine si une exception doit être ignorée.
     */
    private static function shouldIgnore(Throwable $e): bool
    {
        // Vérifier la classe directe
        foreach (self::IGNORED_EXCEPTIONS as $ignoredClass) {
            if ($e instanceof $ignoredClass) {
                // Cas spécial : HttpException — on ignore seulement les codes client
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    return in_array($e->getStatusCode(), self::IGNORED_HTTP_CODES);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Construit le tableau complet de données d'erreur.
     */
    private static function buildErrorData(Throwable $e): array
    {
        $request = request();
        $user    = null;
        $tenant  = null;

        try {
            $user   = auth()->user();
            $tenant = $request->get('current_tenant');
        } catch (\Throwable) {
            // Silencieux — l'auth peut ne pas être disponible
        }

        $data = [
            // Identification
            'exception_class' => get_class($e),
            'code'            => $e->getCode(),
            'severity'        => static::classifySeverity($e),

            // Message & localisation
            'message'         => $e->getMessage(),
            'file'            => $e->getFile(),
            'line'            => $e->getLine(),
            'trace'           => static::formatTrace($e),

            // Contexte Tenant
            'tenant_id'       => $tenant?->id ?? null,
            'tenant_name'     => $tenant?->nom ?? $tenant?->name ?? 'Plateforme Centrale',
            'tenant_db'       => $tenant?->db_name ?? null,

            // Contexte Utilisateur
            'user_id'         => $user?->id ?? null,
            'user_name'       => $user?->name ?? 'Non authentifié',
            'user_email'      => $user?->email ?? null,

            // Contexte Requête
            'url'             => $request->fullUrl(),
            'method'          => $request->method(),
            'ip'              => $request->ip(),
            'user_agent'      => $request->userAgent(),
            'input'           => static::sanitizeInput($request->except(['password', 'password_confirmation', 'token', '_token'])),

            // Métadonnées
            'environment'     => app()->environment(),
            'php_version'     => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timestamp'       => now()->format('d/m/Y H:i:s'),
            'memory_usage'    => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
        ];

        // Exception précédente (cause racine)
        if ($previous = $e->getPrevious()) {
            $data['previous'] = [
                'class'   => get_class($previous),
                'message' => $previous->getMessage(),
                'file'    => $previous->getFile(),
                'line'    => $previous->getLine(),
            ];
        }

        return $data;
    }

    /**
     * Classifie la sévérité de l'exception.
     */
    private static function classifySeverity(Throwable $e): string
    {
        if ($e instanceof \Error || $e instanceof \ErrorException) {
            return 'CRITICAL';
        }

        if ($e instanceof \PDOException || $e instanceof \Illuminate\Database\QueryException) {
            return 'CRITICAL';
        }

        if ($e instanceof \RuntimeException) {
            return 'HIGH';
        }

        return 'ERROR';
    }

    /**
     * Formate la stack trace pour l'affichage dans l'email.
     * Limite la taille pour éviter les emails trop volumineux.
     */
    private static function formatTrace(Throwable $e): string
    {
        $trace = $e->getTraceAsString();

        // Limiter à ~5000 caractères pour l'email
        $maxLength = 5000;
        if (strlen($trace) > $maxLength) {
            $trace = substr($trace, 0, $maxLength) . "\n\n... [TRONQUÉ — " . strlen($e->getTraceAsString()) . " caractères au total]";
        }

        return $trace;
    }

    /**
     * Nettoie les données de la requête pour les inclure dans le rapport.
     * Supprime les données sensibles et limite la taille.
     */
    private static function sanitizeInput(array $input): string
    {
        if (empty($input)) {
            return '(vide)';
        }

        $json = json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // Limiter la taille du payload dans le rapport
        if (strlen($json) > 2000) {
            $json = substr($json, 0, 2000) . "\n... [TRONQUÉ]";
        }

        return $json;
    }
}
