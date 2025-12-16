<?php

namespace App\Repositories\Eloquent;

use App\Models\StudentPurchase;
use App\Repositories\Contracts\StudentPurchaseRepository;

class EloquentStudentPurchaseRepository implements StudentPurchaseRepository
{
    public function create(array $data): StudentPurchase
    {
        return StudentPurchase::create($data);
    }
}