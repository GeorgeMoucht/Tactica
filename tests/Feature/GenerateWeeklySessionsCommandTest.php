<?php

namespace Tests\Feature;

use App\Models\ClassSession;
use App\Models\CourseClass;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateWeeklySessionsCommandTest extends TestCase
{
    use RefreshDatabase;

    // ── Test 1: generates sessions for date range ────────────────

    public function test_generates_sessions_for_date_range(): void
    {
        // Monday class (day_of_week = 1)
        CourseClass::factory()->create([
            'day_of_week' => 1,
            'type'        => 'weekly',
            'active'      => true,
        ]);

        // February 2026 has 4 Mondays: 2, 9, 16, 23
        $this->artisan('sessions:generate', [
            '--from' => '2026-02-01',
            '--to'   => '2026-02-28',
        ])->assertSuccessful();

        $this->assertDatabaseCount('class_sessions', 4);
    }

    // ── Test 2: skips existing sessions ──────────────────────────

    public function test_skips_existing_sessions(): void
    {
        $class = CourseClass::factory()->create([
            'day_of_week' => 1,
            'type'        => 'weekly',
            'active'      => true,
        ]);

        // Pre-create a session using the same method the command uses (firstOrCreate)
        ClassSession::create([
            'class_id'    => $class->id,
            'date'        => '2026-02-02',
            'starts_time' => $class->starts_time,
            'ends_time'   => $class->ends_time,
        ]);

        $this->assertDatabaseCount('class_sessions', 1);

        $this->artisan('sessions:generate', [
            '--from' => '2026-02-01',
            '--to'   => '2026-02-28',
        ])->assertSuccessful();

        // Still 4 records total, no duplicates
        $this->assertDatabaseCount('class_sessions', 4);
    }

    // ── Test 3: ignores inactive classes ─────────────────────────

    public function test_ignores_inactive_classes(): void
    {
        CourseClass::factory()->create([
            'day_of_week' => 1,
            'type'        => 'weekly',
            'active'      => false,
        ]);

        $this->artisan('sessions:generate', [
            '--from' => '2026-02-01',
            '--to'   => '2026-02-28',
        ])->assertSuccessful();

        $this->assertDatabaseCount('class_sessions', 0);
    }

    // ── Test 4: ignores workshop classes ─────────────────────────

    public function test_ignores_workshop_classes(): void
    {
        CourseClass::factory()->create([
            'type'   => 'workshop',
            'active' => true,
        ]);

        $this->artisan('sessions:generate', [
            '--from' => '2026-02-01',
            '--to'   => '2026-02-28',
        ])->assertSuccessful();

        $this->assertDatabaseCount('class_sessions', 0);
    }

    // ── Test 5: default range is current week ────────────────────

    public function test_default_range_is_current_week(): void
    {
        $today    = Carbon::today();
        $monday   = $today->copy()->startOfWeek(Carbon::MONDAY);
        $sunday   = $today->copy()->endOfWeek(Carbon::SUNDAY);

        // Create a class for every day of the week
        for ($dow = 1; $dow <= 7; $dow++) {
            CourseClass::factory()->create([
                'day_of_week' => $dow,
                'type'        => 'weekly',
                'active'      => true,
            ]);
        }

        $this->artisan('sessions:generate')->assertSuccessful();

        // Should create exactly 7 sessions (one per day, Mon-Sun)
        $this->assertDatabaseCount('class_sessions', 7);

        // Verify all dates fall within the current week
        $sessions = ClassSession::all();
        foreach ($sessions as $session) {
            $this->assertTrue(
                $session->date->between($monday, $sunday),
                "Session date {$session->date->toDateString()} is outside current week"
            );
        }
    }
}
