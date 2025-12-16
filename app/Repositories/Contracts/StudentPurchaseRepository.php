<?php

namespace App\Repositories\Contracts;

use App\Models\StudentPurchase;

interface StudentPurchaseRepository
{
    public function create(array $data): StudentPurchase;
}