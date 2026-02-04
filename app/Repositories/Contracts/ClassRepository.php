<?php

namespace App\Repositories\Contracts;

use App\Models\CourseClass;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ClassRepository
{
    public function paginateForList(array $filters = []): LengthAwarePaginator;

    public function findDetail(int $id): ?CourseClass;

    public function createFromArray(array $data): CourseClass;

    public function update(int $id, array $data): ?CourseClass;

    public function teacherHasConflict(
        int $teacherId,
        int $dayOfWeek,
        string $startsTime,
        string $endsTime,
        ?int $excludeClassId = null
    ): bool;
}