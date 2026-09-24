<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

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
        // Set locale to Indonesian for better date formatting
        Carbon::setLocale('id');
        
        // Set application locale to Indonesian
        config(['app.locale' => 'id']);

        Paginator::useBootstrapFive();

        // On the hosting server every generated link must be https: the offline cashier
        // (service worker) only works on https, and a proxy may hand Laravel plain http
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
