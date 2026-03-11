<?php

namespace App\Providers;

use App\Models\GeneralSetting;
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
        $settings = null;
        try {
            if (Schema::hasTable('general_settings')) {
                $settings = GeneralSetting::first();

                if ($settings) {
                    config([
                        'mail.default' => $settings->mail_mailer ?: config('mail.default'),
                        'mail.mailers.smtp.host' => $settings->mail_host ?: config('mail.mailers.smtp.host'),
                        'mail.mailers.smtp.port' => $settings->mail_port ?: config('mail.mailers.smtp.port'),
                        'mail.mailers.smtp.username' => $settings->mail_username ?: config('mail.mailers.smtp.username'),
                        'mail.mailers.smtp.password' => $settings->mail_password ?: config('mail.mailers.smtp.password'),
                        'mail.mailers.smtp.encryption' => $settings->mail_encryption ?: config('mail.mailers.smtp.encryption'),
                        'mail.from.address' => $settings->mail_from_address ?: config('mail.from.address'),
                        'mail.from.name' => $settings->mail_from_name ?: config('mail.from.name'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Keep app booting (especially artisan commands) when DB is temporarily unavailable.
        }
        view()->share('settings', $settings);

        // \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
        //     return $user->hasRole('Admin') ? true : null;
        // });

        Paginator::useBootstrapFour();
    }
}
