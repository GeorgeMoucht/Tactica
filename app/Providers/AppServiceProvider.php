<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Passport::enablePasswordGrant();

        Passport::tokensExpireIn(CarbonInterval::hours(2));
        Passport::refreshTokensExpireIn(CarbonInterval::days(7));
        Passport::personalAccessTokensExpireIn(CarbonInterval::days(30));

        Passport::tokensCan([
            'admin'       => 'System administrator with full access',
            'teacher'     => 'Teacher: manage own classes, students, attendance',
        ]);

        Passport::defaultScopes(['teacher']);

        /**
         * Thats how we create tokens
         * $user->createToken('api', ['admin']);
         * $user->createToken('api', ['teacher']);
         */
    }
}
