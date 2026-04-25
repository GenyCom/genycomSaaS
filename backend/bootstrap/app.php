<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePreflightRequests::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
            \App\Http\Middleware\TenantMiddleware::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Rapport automatique des exceptions par email (asynchrone + rate limited)
        $exceptions->report(function (\Throwable $e) {
            \App\Exceptions\ExceptionMailReporter::report($e);
        });
    })->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('factures:generer-recurrentes')->dailyAt('02:00');
        $schedule->command('factures:check-overdue')->dailyAt('01:00');
        $schedule->command('devis:check-relances')->dailyAt('01:30');
    })->create();
