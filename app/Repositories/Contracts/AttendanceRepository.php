<?php

namespace App\Repositories\Contracts;

use App\Models\SessionAttendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AttendanceRepository
{
    public function findBySession(int $sessionId): Collection;

    public function upsert(int $sessionId, int $studentId, string $status, ?string $notes = null): SessionAttendance;

    public function bulkUpsert(int $sessionId, array $records): void;

    public function getBySessionAndStudent(int $sessionId, int $studentId): ?SessionAttendance;

    public function paginateSessionsByClass(int $classId, array $filters = []): LengthAwarePaginator;

    public function getStudentSummaryByClass(int $classId, ?string $from, ?string $to): Collection;
}
