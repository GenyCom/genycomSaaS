<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\MouvementStock;
use App\Observers\MouvementStockObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrement de l'Observer pour les stocks
        MouvementStock::observe(MouvementStockObserver::class);
    }
}
