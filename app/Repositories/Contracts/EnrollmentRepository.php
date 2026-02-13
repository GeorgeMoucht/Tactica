<?php

namespace App\Repositories\Contracts;

use App\Models\ClassEnrollment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface EnrollmentRepository
{
    public function findById(int $id): ?ClassEnrollment;

    public function isStudentEnrolled(int $studentId, int $classId): bool;

    public function countActiveEnrollments(int $classId): int;

    public function create(array $data): ClassEnrollment;

    public function updateStatus(int $id, string $status, ?string $withdrawnAt = null): ?ClassEnrollment;

    public function findByStudent(int $studentId): Collection;

    public function update(int $id, array $data): ?ClassEnrollment;

    public function paginateByClass(int $classId, array $filters = []): LengthAwarePaginator;
}
