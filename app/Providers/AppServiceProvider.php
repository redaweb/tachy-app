<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // Ne pas oublier cet import

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force l'utilisation des vues de pagination Bootstrap 5
        Paginator::useBootstrapFive();

        // Ou si vous utilisez Bootstrap 4 :
        // Paginator::useBootstrapFour();
    }
}
