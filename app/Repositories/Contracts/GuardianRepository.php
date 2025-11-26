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

    /**
     * Pagiante guardians for list view.
     * 
     * @param array{query?:string|null, perPage?:int|null} $filters
     */
    public function paginateForList(array $filters = []): LengthAwarePaginator;

    /**
     * Load guardian with related students.
     */
    public function findWithStudents(int $id): ?Guardian;

    /**
     * Update guardian and return fresh model.
     */
    public function update(int $id, array $data): ?Guardian;
}