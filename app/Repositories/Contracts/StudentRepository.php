<?php

namespace App\Repositories\Contracts;

use App\Data\DTO\Registration\StudentDTO;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentRepository
{
    
    public function paginateForList(array $filters = []): LengthAwarePaginator;
    
    public function findWithGuardians(int $id): ?Student;

    public function update(int $id, array $data): ?Student;

    public function createFromArray(array $data): Student;
    
    public function create(StudentDTO $dto): Student;
}