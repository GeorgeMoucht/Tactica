<?php

namespace App\Repositories\Contracts;

use App\Models\MonthlyDue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MonthlyDueRepository
{
    public function findById(int $id): ?MonthlyDue;

    public function create(array $data): MonthlyDue;

    public function update(int $id, array $data): ?MonthlyDue;

    public function findByStudent(int $studentId, array $filters = []): Collection;

    public function paginateByStudent(int $studentId, array $filters = []): LengthAwarePaginator;

    public function existsForPeriod(int $studentId, int $classId, int $year, int $month): bool;

    public function getPendingByStudent(int $studentId): Collection;

    public function sumPaidByStudent(int $studentId): float;

    public function sumPendingByStudent(int $studentId): float;
}
