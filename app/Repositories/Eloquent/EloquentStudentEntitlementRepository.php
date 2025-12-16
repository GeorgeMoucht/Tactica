<?php

namespace App\Repositories\Eloquent;

use App\Models\StudentEntitlement;
use App\Repositories\Contracts\StudentEntitlementRepository;

class EloquentStudentEntitlementRepository implements StudentEntitlementRepository
{
    public function create(array $data): StudentEntitlement
    {
        return StudentEntitlement::create($data);
    }
}
