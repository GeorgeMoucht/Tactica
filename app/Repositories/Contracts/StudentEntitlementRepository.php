<?php

namespace App\Repositories\Contracts;

use App\Models\StudentEntitlement;

interface StudentEntitlementRepository
{
    public function create(array $data): StudentEntitlement;
}