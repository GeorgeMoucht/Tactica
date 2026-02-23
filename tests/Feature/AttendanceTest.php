<?php

namespace Tests\Feature;

use App\Models\ClassEnrollment;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\MonthlyDue;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(array $attrs = []): User
    {
        $user = User::factory()->create(array_merge(['role' => 'teacher'], $attrs));
        $this->actingAs($user, 'api');

        return $user;
    }

    private function createClassForDay(int $dayOfWeek, array $overrides = []): CourseClass
    {
        return CourseClass::factory()->create(array_merge([
            'day_of_week' => $dayOfWeek,
            'type'        => 'weekly',
            'active'      => true,
        ], $overrides));
    }

    // ── Test 1: today-sessions returns classes for today ──────────

    public function test_today_sessions_returns_classes_for_today(): void
    {
        $this->authUser();

        $todayDow = Carbon::today()->dayOfWeekIso;
        $tomorrowDow = Carbon::tomorrow()->dayOfWeekIso;

        $this->createClassForDay($todayDow);
        $this->createClassForDay($todayDow);
        $this->createClassForDay($tomorrowDow);

        $response = $this->getJson('/api/v1/dashboard/today-sessions');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    // ── Test 2: auto-creates session records ─────────────────────

    public function test_today_sessions_auto_creates_session_records(): void
    {
        $this->authUser();

        $todayDow = Carbon::today()->dayOfWeekIso;
        $this->createClassForDay($todayDow);
        $this->createClassForDay($todayDow);

        $this->assertDatabaseCount('class_sessions', 0);

        $this->getJson('/api/v1/dashboard/today-sessions')->assertOk();

        $this->assertDatabaseCount('class_sessions', 2);
    }

    // ── Test 3: roster shows enrolled students ───────────────────

    public function test_roster_shows_enrolled_students(): void
    {
        $this->authUser();

        $class   = CourseClass::factory()->create();
        $session = ClassSession::factory()->create(['class_id' => $class->id]);

        // 3 active enrollments
        for ($i = 0; $i < 3; $i++) {
            ClassEnrollment::factory()->create([
                'class_id' => $class->id,
                'status'   => 'active',
            ]);
        }

        // 1 withdrawn enrollment
        ClassEnrollment::factory()->withdrawn()->create([
            'class_id' => $class->id,
        ]);

        $response = $this->getJson("/api/v1/sessions/{$session->id}/attendance");

        $response->assertOk()
            ->assertJsonCount(3, 'data.students');
    }

    // ── Test 4: roster shows debt warnings ───────────────────────

    public function test_roster_shows_debt_warnings(): void
    {
        $this->authUser();

        $class   = CourseClass::factory()->create();
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $student = Student::factory()->create();

        ClassEnrollment::factory()->create([
            'student_id' => $student->id,
            'class_id'   => $class->id,
        ]);

        MonthlyDue::create([
            'student_id'   => $student->id,
            'class_id'     => $class->id,
            'period_year'  => now()->year,
            'period_month' => now()->month,
            'amount'       => 85.00,
            'status'       => 'pending',
        ]);

        $response = $this->getJson("/api/v1/sessions/{$session->id}/attendance");

        $response->assertOk()
            ->assertJsonPath('data.students.0.has_debt', true);

        $data = $response->json('data.students.0');
        $this->assertEquals(85.00, $data['outstanding_amount']);
    }

    // ── Test 5: debt_summary only contains debtors ───────────────

    public function test_roster_debt_summary_only_debtors(): void
    {
        $this->authUser();

        $class   = CourseClass::factory()->create();
        $session = ClassSession::factory()->create(['class_id' => $class->id]);

        $studentWithDebt = Student::factory()->create();
        $studentNoDebt   = Student::factory()->create();

        ClassEnrollment::factory()->create([
            'student_id' => $studentWithDebt->id,
            'class_id'   => $class->id,
        ]);
        ClassEnrollment::factory()->create([
            'student_id' => $studentNoDebt->id,
            'class_id'   => $class->id,
        ]);

        MonthlyDue::create([
            'student_id'   => $studentWithDebt->id,
            'class_id'     => $class->id,
            'period_year'  => now()->year,
            'period_month' => now()->month,
            'amount'       => 85.00,
            'status'       => 'pending',
        ]);

        $response = $this->getJson("/api/v1/sessions/{$session->id}/attendance");

        $response->assertOk()
            ->assertJsonCount(1, 'data.debt_summary')
            ->assertJsonPath('data.debt_summary.0.student_id', $studentWithDebt->id);
    }

    // ── Test 6: can mark attendance ──────────────────────────────

    public function test_can_mark_attendance(): void
    {
        $teacher = $this->authUser();

        $class   = CourseClass::factory()->create();
        $session = ClassSession::factory()->create(['class_id' => $class->id]);

        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();

        ClassEnrollment::factory()->create(['student_id' => $student1->id, 'class_id' => $class->id]);
        ClassEnrollment::factory()->create(['student_id' => $student2->id, 'class_id' => $class->id]);

        $response = $this->postJson("/api/v1/sessions/{$session->id}/attendance", [
            'conducted_by' => $teacher->id,
            'attendances'  => [
                ['student_id' => $student1->id, 'status' => 'present'],
                ['student_id' => $student2->id, 'status' => 'absent'],
            ],
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('session_attendances', [
            'session_id' => $session->id,
            'student_id' => $student1->id,
            'status'     => 'present',
        ]);
        $this->assertDatabaseHas('session_attendances', [
            'session_id' => $session->id,
            'student_id' => $student2->id,
            'status'     => 'absent',
        ]);
    }

    // ── Test 7: attendance is idempotent ─────────────────────────

    public function test_attendance_is_idempotent(): void
    {
        $teacher = $this->authUser();

        $class   = CourseClass::factory()->create();
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $student = Student::factory()->create();

        ClassEnrollment::factory()->create(['student_id' => $student->id, 'class_id' => $class->id]);

        $payload = [
            'conducted_by' => $teacher->id,
            'attendances'  => [
                ['student_id' => $student->id, 'status' => 'absent'],
            ],
        ];

        $this->postJson("/api/v1/sessions/{$session->id}/attendance", $payload)->assertCreated();

        // Second call with updated status
        $payload['attendances'][0]['status'] = 'present';
        $this->postJson("/api/v1/sessions/{$session->id}/attendance", $payload)->assertCreated();

        $this->assertDatabaseCount('session_attendances', 1);
        $this->assertDatabaseHas('session_attendances', [
            'session_id' => $session->id,
            'student_id' => $student->id,
            'status'     => 'present',
        ]);
    }

    // ── Test 8: roster shows existing attendance ─────────────────

    public function test_roster_shows_existing_attendance(): void
    {
        $this->authUser();

        $class   = CourseClass::factory()->create();
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $student = Student::factory()->create();

        ClassEnrollment::factory()->create(['student_id' => $student->id, 'class_id' => $class->id]);

        SessionAttendance::create([
            'session_id' => $session->id,
            'student_id' => $student->id,
            'status'     => 'present',
        ]);

        $response = $this->getJson("/api/v1/sessions/{$session->id}/attendance");

        $response->assertOk()
            ->assertJsonPath('data.students.0.attendance_status', 'present');
    }

    // ── Test 9: marking attendance sets conducted_by and marked_by ─

    public function test_marking_attendance_sets_conducted_by_and_marked_by(): void
    {
        $submitter = $this->authUser();
        $conductor = User::factory()->create(['role' => 'teacher']);

        $class   = CourseClass::factory()->create();
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $student = Student::factory()->create();

        ClassEnrollment::factory()->create(['student_id' => $student->id, 'class_id' => $class->id]);

        $this->postJson("/api/v1/sessions/{$session->id}/attendance", [
            'conducted_by' => $conductor->id,
            'attendances'  => [
                ['student_id' => $student->id, 'status' => 'present'],
            ],
        ])->assertCreated();

        $this->assertDatabaseHas('class_sessions', [
            'id'           => $session->id,
            'conducted_by' => $conductor->id,
            'marked_by'    => $submitter->id,
        ]);
    }

    // ── Test 10: conducted_by IS updated on resubmission ──────────

    public function test_conducted_by_is_updated_on_resubmission(): void
    {
        $teacher1 = $this->authUser();
        $teacher2 = User::factory()->create(['role' => 'teacher']);

        $class   = CourseClass::factory()->create();
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $student = Student::factory()->create();

        ClassEnrollment::factory()->create(['student_id' => $student->id, 'class_id' => $class->id]);

        // First submission: conducted_by = teacher1
        $this->postJson("/api/v1/sessions/{$session->id}/attendance", [
            'conducted_by' => $teacher1->id,
            'attendances'  => [
                ['student_id' => $student->id, 'status' => 'present'],
            ],
        ])->assertCreated();

        // Second submission: change conducted_by to teacher2
        $this->postJson("/api/v1/sessions/{$session->id}/attendance", [
            'conducted_by' => $teacher2->id,
            'attendances'  => [
                ['student_id' => $student->id, 'status' => 'absent'],
            ],
        ])->assertCreated();

        // conducted_by should now be teacher2
        $this->assertDatabaseHas('class_sessions', [
            'id'           => $session->id,
            'conducted_by' => $teacher2->id,
        ]);
    }

    // ── Test 11: conducted_by is required ─────────────────────────

    public function test_conducted_by_is_required(): void
    {
        $this->authUser();

        $class   = CourseClass::factory()->create();
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $student = Student::factory()->create();

        ClassEnrollment::factory()->create(['student_id' => $student->id, 'class_id' => $class->id]);

        $response = $this->postJson("/api/v1/sessions/{$session->id}/attendance", [
            'attendances' => [
                ['student_id' => $student->id, 'status' => 'present'],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['conducted_by']);
    }

    // ── Test 12: roster includes teachers list ────────────────────

    public function test_roster_includes_teachers_list(): void
    {
        $teacher = $this->authUser();

        $class   = CourseClass::factory()->create();
        $session = ClassSession::factory()->create(['class_id' => $class->id]);

        $response = $this->getJson("/api/v1/sessions/{$session->id}/attendance");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['teachers' => [['id', 'name']]]]);

        $teacherIds = collect($response->json('data.teachers'))->pluck('id');
        $this->assertTrue($teacherIds->contains($teacher->id));
    }
}
