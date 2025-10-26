<?php

namespace App\Repositories\Contracts;

use App\Data\DTO\Registration\GuardianDTO;
use App\Models\Guardian;
use Illuminate\Pagination\LengthAwarePaginator;

interface GuardianRepository
{
    public function create(GuardianDTO $dto): Guardian;
}