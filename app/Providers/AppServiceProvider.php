<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        // \Illuminate\Support\Facades\View::share('settings', getSettings());

        if (Schema::hasTable('general_settings')) {
            $settings = \App\Models\GeneralSetting::first();
            view()->share('settings', $settings);
        }

        // \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
        //     return $user->hasRole('Admin') ? true : null;
        // });

        Paginator::useBootstrapFour();
    }
}
