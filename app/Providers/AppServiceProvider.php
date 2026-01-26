<?php

namespace App\Providers;

use App\Helpers\RouteHelper;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

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
        //
        Paginator::useBootstrapFour();

        RateLimiter::for('jsons', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Registrar Blade directive para verificar acceso a rutas
        \Blade::if('canAccessRoute', function ($routeName) {
            return \App\Helpers\RouteHelper::canAccessRoute($routeName);
        });
    }
}
