<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Config;
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
        // Customize password reset URL for SPA frontend
        ResetPassword::createUrlUsing(function ($user, string $token) {
            $frontendUrl = Config::get('app.frontend_url', Config::get('app.url'));

            return $frontendUrl.'/reset-password?token='.$token.'&email='.urlencode($user->email);
        });
    }
}
