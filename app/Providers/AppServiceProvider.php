<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
    }
}
