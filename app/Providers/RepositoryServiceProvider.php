<?php

namespace App\Providers;

use App\Contracts\Auth\TokenIssuer;
use App\Infrastructure\Auth\PassportTokenIssuer;
use App\Repositories\Contracts\ClassRepository;
use App\Repositories\Contracts\EnrollmentRepository;
use App\Repositories\Contracts\GuardianRepository;
use App\Repositories\Contracts\MembershipRepository;
use App\Repositories\Contracts\ProductRepository;
use App\Repositories\Contracts\StudentEntitlementRepository;
use App\Repositories\Contracts\StudentPurchaseRepository;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Eloquent\EloquentClassRepository;
use App\Repositories\Eloquent\EloquentEnrollmentRepository;
use App\Repositories\Eloquent\EloquentGuardianRepository;
use App\Repositories\Eloquent\EloquentMembershipRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Repositories\Eloquent\EloquentStudentEntitlementRepository;
use App\Repositories\Eloquent\EloquentStudentPurchaseRepository;
use App\Repositories\Eloquent\EloquentStudentRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Contracts\MonthlyDueRepository;
use App\Repositories\Eloquent\EloquentMonthlyDueRepository;

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

        $this->app->bind(MembershipRepository::class, EloquentMembershipRepository::class);
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);

        $this->app->bind(ClassRepository::class, EloquentClassRepository::class);
        $this->app->bind(EnrollmentRepository::class, EloquentEnrollmentRepository::class);
        $this->app->bind(MonthlyDueRepository::class, EloquentMonthlyDueRepository::class);
    }
}