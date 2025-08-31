<?php

namespace App\Providers;

use App\Contracts\Auth\TokenIssuer;
use App\Infrastructure\Auth\PassportTokenIssuer;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Eloquent\EloquentUserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(TokenIssuer::class, PassportTokenIssuer::class);
    }
}