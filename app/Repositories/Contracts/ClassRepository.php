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

    public function delete(int $id): bool;

    public function toggleActive(int $id): ?CourseClass;

    public function teacherHasConflict(
        int $teacherId,
        int $dayOfWeek,
        string $startsTime,
        string $endsTime,
        ?int $excludeClassId = null
    ): bool;

    public function teacherHasWorkshopConflict(
        int $teacherId,
        array $sessions,
        ?int $excludeClassId = null
    ): bool;
}