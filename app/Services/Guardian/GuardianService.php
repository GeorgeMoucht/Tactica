<?php

namespace App\Services\Guardian;

use App\Models\Guardian;
use App\Repositories\Contracts\GuardianRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class GuardianService
{
    public function __construct(
        private GuardianRepository $guardians
    ) {}

    public function list(array $filters): LengthAwarePaginator
    {
        return $this->guardians->paginateForList($filters);
    }

    public function detail(int $id): ?Guardian
    {
        return $this->guardians->findWithStudents($id);
    }

    public function update(int $id, array $data): ?Guardian
    {
        return $this->guardians->update($id, $data);
    }

    public function create(array $data): Guardian
    {
        return $this->guardians->createFromArray($data);
    }
}