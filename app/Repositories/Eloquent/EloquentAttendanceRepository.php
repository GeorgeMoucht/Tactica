<?php

namespace App\Repositories\Eloquent;

use App\Models\ClassSession;
use App\Models\SessionAttendance;
use App\Repositories\Contracts\AttendanceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentAttendanceRepository implements AttendanceRepository
{
    public function findBySession(int $sessionId): Collection
    {
        return SessionAttendance::with('student')
            ->where('session_id', $sessionId)
            ->get();
    }

    public function upsert(int $sessionId, int $studentId, string $status, ?string $notes = null): SessionAttendance
    {
        return SessionAttendance::updateOrCreate(
            ['session_id' => $sessionId, 'student_id' => $studentId],
            ['status' => $status, 'notes' => $notes]
        );
    }

    public function bulkUpsert(int $sessionId, array $records): void
    {
        $rows = array_map(fn (array $record) => [
            'session_id' => $sessionId,
            'student_id' => $record['student_id'],
            'status'     => $record['status'],
            'notes'      => $record['notes'] ?? null,
        ], $records);

        SessionAttendance::upsert(
            $rows,
            ['session_id', 'student_id'],
            ['status', 'notes']
        );
    }

    public function getBySessionAndStudent(int $sessionId, int $studentId): ?SessionAttendance
    {
        return SessionAttendance::where('session_id', $sessionId)
            ->where('student_id', $studentId)
            ->first();
    }

    public function paginateSessionsByClass(int $classId, array $filters = []): LengthAwarePaginator
    {
        return ClassSession::where('class_id', $classId)
            ->with(['attendances.student', 'conductor'])
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->where('date', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->where('date', '<=', $v))
            ->orderByDesc('date')
            ->paginate((int) ($filters['perPage'] ?? 15));
    }

    public function getStudentSummaryByClass(int $classId, ?string $from, ?string $to): Collection
    {
        return DB::table('session_attendances')
            ->select([
                'session_attendances.student_id',
                'students.first_name', 'students.last_name',
                DB::raw("COUNT(*) as total_sessions"),
                DB::raw("SUM(CASE WHEN session_attendances.status = 'present' THEN 1 ELSE 0 END) as total_present"),
                DB::raw("SUM(CASE WHEN session_attendances.status = 'absent' THEN 1 ELSE 0 END) as total_absent"),
            ])
            ->join('class_sessions', 'class_sessions.id', '=', 'session_attendances.session_id')
            ->join('students', 'students.id', '=', 'session_attendances.student_id')
            ->where('class_sessions.class_id', $classId)
            ->when($from, fn ($q) => $q->where('class_sessions.date', '>=', $from))
            ->when($to, fn ($q) => $q->where('class_sessions.date', '<=', $to))
            ->groupBy('session_attendances.student_id', 'students.first_name', 'students.last_name')
            ->get();
    }
}
