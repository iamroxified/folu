<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Share school settings globally, but only if table exists
        try {
            view()->share('schoolSettings', \App\Models\SchoolSetting::first());
        } catch (\Exception $e) {
            // Table might not exist during migration
            view()->share('schoolSettings', null);
        }
    }
}
