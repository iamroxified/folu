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
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'student_fee' => \App\Models\StudentFee::class,
            'additional_charge' => \App\Models\AdditionalCharge::class,
            'App\Models\StudentFee' => \App\Models\StudentFee::class,
            'App\Models\AdditionalCharge' => \App\Models\AdditionalCharge::class,
        ]);

        // Share school settings globally, but only if table exists
        try {
            view()->share('schoolSettings', \App\Models\SchoolSetting::first());
        } catch (\Exception $e) {
            // Table might not exist during migration
            view()->share('schoolSettings', null);
        }
    }
}
