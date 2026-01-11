<?php

namespace App\Repositories\Contracts;

use App\Models\StudentEntitlement;
use Illuminate\Support\Collection;

interface StudentEntitlementRepository
{
    public function create(array $data): StudentEntitlement;

    /**
     * Return membership (registration) entitlements for a student
     */
    public function findMembershipsByStudent(int $studentId): Collection;
}