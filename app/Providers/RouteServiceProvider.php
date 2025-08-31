<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('auth-login', function (Request $request) {
            $email = (string) $request->input('email', 'guest');
            return [Limit::perMinute(5)->by($request->ip() . '|' . $email)];
        });

        // Token refresh throttling
        RateLimiter::for('auth-refresh', function (Request $request) {
            return [Limit::perMinute(30)->by($request->ip())];
        });

        // Registration throttling
        RateLimiter::for('auth-register', function (Request $request) {
            return [Limit::perMinute(10)->by($request->ip())];
        });

        // Password change throttling
        RateLimiter::for('auth-change-password', function (Request $request) {
            $userId = optional($request->user())->id ?? 'guest';
            return [Limit::perMinute(10)->by($request->ip . '|' . $userId)];
        });
    }
}