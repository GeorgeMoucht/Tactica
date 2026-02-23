<?php

namespace App\Services\Attendance;

use App\Exceptions\NotFoundException;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Repositories\Contracts\AttendanceRepository;
use App\Repositories\Contracts\MonthlyDueRepository;
use App\Repositories\Contracts\UserRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AttendanceService
{
    private AttendanceRepository $attendances;
    private MonthlyDueRepository $monthlyDues;
    private UserRepository $users;

    public function __construct(
        AttendanceRepository $attendances,
        MonthlyDueRepository $monthlyDues,
        UserRepository $users,
    ) {
        $this->attendances = $attendances;
        $this->monthlyDues = $monthlyDues;
        $this->users = $users;
    }

    public function getTodaySessions(): Collection
    {
        $today     = Carbon::today();
        $dayOfWeek = $today->dayOfWeekIso; // Monday=1 ... Sunday=7

        // Auto-create sessions for weekly classes scheduled on today's weekday
        CourseClass::where('type', 'weekly')
            ->where('active', true)
            ->where('day_of_week', $dayOfWeek)
            ->each(fn (CourseClass $class) => $this->getOrCreateSession($class->id, $today));

        // Return ALL sessions for today (including manually seeded / ad-hoc ones)
        return ClassSession::where('date', $today)
            ->with(['courseClass.teacher', 'courseClass.activeEnrollments', 'conductor', 'attendances'])
            ->whereHas('courseClass', fn ($q) => $q->where('active', true))
            ->get();
    }

    public function getOrCreateSession(int $classId, Carbon $date): ClassSession
    {
        $class = CourseClass::findOrFail($classId);

        return ClassSession::firstOrCreate(
            ['class_id' => $classId, 'date' => $date->startOfDay()],
            [
                'starts_time' => $class->starts_time,
                'ends_time'   => $class->ends_time,
            ]
        );
    }

    public function getSessionRoster(int $sessionId): array
    {
        $session = ClassSession::with(['courseClass.teacher', 'conductor'])->find($sessionId);

        if (!$session) {
            throw new NotFoundException('Session not found.');
        }

        $courseClass = $session->courseClass;

        // Get active enrolled students for this class
        $enrolledStudents = $courseClass->activeEnrollments()
            ->with('student')
            ->get()
            ->pluck('student');

        // Get existing attendance records
        $existingAttendances = $this->attendances->findBySession($sessionId)
            ->keyBy('student_id');

        $debtSummary = [];
        $students    = [];

        foreach ($enrolledStudents as $student) {
            $outstanding = $this->monthlyDues->sumPendingByStudent($student->id);
            $attendance  = $existingAttendances->get($student->id);

            $hasDebt = $outstanding > 0;

            if ($hasDebt) {
                $debtSummary[] = [
                    'student_id'         => $student->id,
                    'name'               => $student->name,
                    'outstanding_amount' => $outstanding,
                ];
            }

            $students[] = [
                'student'            => $student,
                'attendance_status'  => $attendance?->status,
                'has_debt'           => $hasDebt,
                'outstanding_amount' => $outstanding,
            ];
        }

        return [
            'session'      => $session,
            'teachers'     => $this->users->listTeachers()->map(fn ($t) => [
                'id'   => $t->id,
                'name' => $t->name,
            ]),
            'debt_summary' => $debtSummary,
            'students'     => $students,
        ];
    }

    public function markAttendance(int $sessionId, array $attendances, int $conductedBy, int $markedBy): void
    {
        $session = ClassSession::find($sessionId);

        if (!$session) {
            throw new NotFoundException('Session not found.');
        }

        $this->attendances->bulkUpsert($sessionId, $attendances);

        $session->update([
            'conducted_by' => $conductedBy,
            'marked_by'    => $markedBy,
        ]);
    }

    public function getClassAttendanceHistory(int $classId, array $filters): LengthAwarePaginator
    {
        $class = CourseClass::find($classId);

        if (!$class) {
            throw new NotFoundException('Class not found.');
        }

        return $this->attendances->paginateSessionsByClass($classId, $filters);
    }

    public function getClassAttendanceSummary(int $classId, ?string $from, ?string $to): array
    {
        $class = CourseClass::find($classId);

        if (!$class) {
            throw new NotFoundException('Class not found.');
        }

        $totalSessions = ClassSession::where('class_id', $classId)
            ->when($from, fn ($q) => $q->where('date', '>=', $from))
            ->when($to, fn ($q) => $q->where('date', '<=', $to))
            ->count();

        return [
            'class'          => $class,
            'total_sessions' => $totalSessions,
            'students'       => $this->attendances->getStudentSummaryByClass($classId, $from, $to),
        ];
    }
}
