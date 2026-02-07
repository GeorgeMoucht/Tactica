<?php

namespace App\Services\Guardian;

use App\Exceptions\NotFoundException;
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

    public function detail(int $id): Guardian
    {
        $guardian = $this->guardians->findWithStudents($id);
        if (!$guardian) {
            throw new NotFoundException('Guardian not found.');
        }
        return $guardian;
    }

    public function update(int $id, array $data): Guardian
    {
        $guardian = $this->guardians->update($id, $data);
        if (!$guardian) {
            throw new NotFoundException('Guardian not found.');
        }
        return $guardian;
    }

    public function create(array $data): Guardian
    {
        return $this->guardians->createFromArray($data);
    }
}