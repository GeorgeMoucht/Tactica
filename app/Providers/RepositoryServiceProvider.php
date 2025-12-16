<?php

namespace App\Providers;

use App\Contracts\Auth\TokenIssuer;
use App\Infrastructure\Auth\PassportTokenIssuer;
use App\Repositories\Contracts\GuardianRepository;
use App\Repositories\Contracts\StudentEntitlementRepository;
use App\Repositories\Contracts\StudentPurchaseRepository;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Eloquent\EloquentGuardianRepository;
use App\Repositories\Eloquent\EloquentStudentEntitlementRepository;
use App\Repositories\Eloquent\EloquentStudentPurchaseRepository;
use App\Repositories\Eloquent\EloquentStudentRepository;
use App\Repositories\Eloquent\EloquentUserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(TokenIssuer::class, PassportTokenIssuer::class);

        $this->app->bind(GuardianRepository::class, EloquentGuardianRepository::class);
        $this->app->bind(StudentRepository::class, EloquentStudentRepository::class);

        $this->app->bind(StudentPurchaseRepository::class, EloquentStudentPurchaseRepository::class);
        $this->app->bind(StudentEntitlementRepository::class, EloquentStudentEntitlementRepository::class);
    }
}