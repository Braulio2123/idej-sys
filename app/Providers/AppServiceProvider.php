<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password as PasswordRule;

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
        PasswordRule::defaults(fn () => PasswordRule::min(12)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols());

        if (config('idej_security.force_https')) {
            URL::forceScheme('https');
        }
    }
}
