<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassSession;
use App\Models\ClassEnrollment;
use App\Models\CourseClass;
use App\Models\SessionAttendance;
use App\Models\User;
use Carbon\Carbon;

/**
 * Generates 10 weeks of historical attendance data + today's sessions.
 *
 * For each weekly class with active enrollments:
 *  - Creates sessions for the past 10 weeks
 *  - Marks attendance with realistic varied patterns per student
 *  - Creates today's sessions (some with partial attendance)
 *
 * Safe to run multiple times — uses firstOrCreate and cleans existing attendance.
 *
 * Usage:
 *   php artisan db:seed --class=AttendanceTestSeeder
 */
class AttendanceTestSeeder extends Seeder
{
    /**
     * Student attendance profiles — determines how likely each student is to attend.
     * Key = student position index within each class, Value = probability of attending (0.0–1.0).
     */
    private const ATTENDANCE_PROFILES = [
        0  => 0.95, // excellent
        1  => 0.90,
        2  => 0.85,
        3  => 0.80,
        4  => 0.75,
        5  => 0.60, // moderate
        6  => 0.70,
        7  => 0.50, // low
        8  => 0.85,
        9  => 0.45, // very low
        10 => 0.80,
        11 => 0.90,
    ];

    public function run(): void
    {
        $teacher = User::where('email', 'teacher@tactica.com')->first();
        $admin   = User::where('email', 'admin@tactica.com')->first();

        if (!$teacher || !$admin) {
            $this->command?->warn('AttendanceTestSeeder: users not found. Run UserSeeder first.');
            return;
        }

        $conductors = [$teacher->id, $admin->id];

        $classesWithStudents = CourseClass::whereHas('activeEnrollments')
            ->where('type', 'weekly')
            ->get();

        if ($classesWithStudents->isEmpty()) {
            $this->command?->warn('AttendanceTestSeeder: No classes with active enrollments found.');
            return;
        }

        $today = Carbon::today();
        $weeksBack = 10;
        $sessionsCreated = 0;
        $attendanceCreated = 0;

        foreach ($classesWithStudents as $class) {
            $enrolledStudentIds = ClassEnrollment::where('class_id', $class->id)
                ->where('status', 'active')
                ->pluck('student_id')
                ->unique()
                ->values();

            if ($enrolledStudentIds->isEmpty()) continue;

            $classDow = (int) $class->day_of_week;

            /*
            |------------------------------------------------------------------
            | Historical sessions (past 10 weeks)
            |------------------------------------------------------------------
            */
            for ($w = $weeksBack; $w >= 1; $w--) {
                $sessionDate = $today->copy()->subWeeks($w);

                // Align to correct day of week
                $currentDow = $sessionDate->dayOfWeekIso;
                $diff = $classDow - $currentDow;
                $sessionDate->addDays($diff);

                // Skip if date is in the future (shouldn't happen but safety check)
                if ($sessionDate->gte($today)) continue;

                $conductorId = $conductors[array_rand($conductors)];

                $session = ClassSession::firstOrCreate(
                    [
                        'class_id' => $class->id,
                        'date'     => $sessionDate->format('Y-m-d'),
                    ],
                    [
                        'starts_time'  => $class->starts_time,
                        'ends_time'    => $class->ends_time,
                        'conducted_by' => $conductorId,
                    ]
                );
                $sessionsCreated++;

                // Clear any existing attendance for this session
                SessionAttendance::where('session_id', $session->id)->delete();

                // Generate attendance for each enrolled student
                foreach ($enrolledStudentIds as $idx => $studentId) {
                    $profile = self::ATTENDANCE_PROFILES[$idx] ?? 0.75;

                    // Use deterministic seed so results are consistent
                    $seed = ($studentId * 31 + $session->id * 17 + $w) % 100;
                    $isPresent = $seed < ($profile * 100);

                    SessionAttendance::create([
                        'session_id' => $session->id,
                        'student_id' => $studentId,
                        'status'     => $isPresent ? 'present' : 'absent',
                        'notes'      => (!$isPresent && $seed % 3 === 0) ? 'Δικαιολογημένη απουσία' : null,
                    ]);
                    $attendanceCreated++;
                }
            }

            /*
            |------------------------------------------------------------------
            | Today's session
            |------------------------------------------------------------------
            */
            // Ensure the class day_of_week matches today so getTodaySessions() finds it
            $todayDow = $today->dayOfWeekIso;
            if ($classDow !== $todayDow) {
                $class->update(['day_of_week' => $todayDow]);
            }

            $todaySession = ClassSession::firstOrCreate(
                [
                    'class_id' => $class->id,
                    'date'     => $today->format('Y-m-d'),
                ],
                [
                    'starts_time'  => $class->starts_time,
                    'ends_time'    => $class->ends_time,
                    'conducted_by' => null, // Not yet conducted
                ]
            );
            $sessionsCreated++;

            // For the first class only: mark partial attendance on today's session
            if ($class->id === $classesWithStudents->first()->id) {
                SessionAttendance::where('session_id', $todaySession->id)->delete();

                foreach ($enrolledStudentIds->take(3) as $i => $studentId) {
                    SessionAttendance::create([
                        'session_id' => $todaySession->id,
                        'student_id' => $studentId,
                        'status'     => $i < 2 ? 'present' : 'absent',
                        'notes'      => $i === 2 ? 'Τηλεφώνησε — αρρώστησε' : null,
                    ]);
                    $attendanceCreated++;
                }
            }
        }

        $this->command?->info("AttendanceTestSeeder: Created {$sessionsCreated} sessions, {$attendanceCreated} attendance records.");
        $this->command?->info("  Historical data: {$weeksBack} weeks back for {$classesWithStudents->count()} classes.");
        $this->command?->info("  Today: {$today->format('Y-m-d')}");

        foreach ($classesWithStudents as $class) {
            $studentCount = ClassEnrollment::where('class_id', $class->id)
                ->where('status', 'active')
                ->count();
            $this->command?->info("  • {$class->title} — {$studentCount} enrolled students");
        }
    }
}
