<?php

namespace App\Repositories\Contracts;

use App\Data\DTO\Registration\GuardianDTO;
use App\Models\Guardian;
use Illuminate\Pagination\LengthAwarePaginator;

interface GuardianRepository
{
    public function create(GuardianDTO $dto): Guardian;

    public function createFromArray(array $data): Guardian;

    /**
     * Try to find existing guardian by email or phone (avoid duplicate ).
     * Returns null if neither provided or no match found.
     */
    public function findByEmailOrPhone(?string $email, ?string $phone): ?Guardian;
}