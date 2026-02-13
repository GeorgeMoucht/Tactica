<?php

namespace Tests\Feature;

use App\Models\ClassEnrollment;
use App\Models\CourseClass;
use App\Models\MonthlyDue;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateMonthlyDuesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_dues_for_all_active_enrollments(): void
    {
        $student = Student::factory()->create();
        $class1  = CourseClass::factory()->create();
        $class2  = CourseClass::factory()->create();

        ClassEnrollment::factory()->create([
            'student_id' => $student->id,
            'class_id'   => $class1->id,
        ]);
        ClassEnrollment::factory()->create([
            'student_id' => $student->id,
            'class_id'   => $class2->id,
        ]);

        $this->artisan('dues:generate')
            ->assertSuccessful();

        $this->assertDatabaseCount('monthly_dues', 2);
        $this->assertDatabaseHas('monthly_dues', [
            'student_id'       => $student->id,
            'class_id'         => $class1->id,
            'period_year'      => now()->year,
            'period_month'     => now()->month,
            'amount'           => '40.00',
            'base_price'       => '40.00',
            'discount_applied' => '0.00',
            'price_override'   => false,
            'status'           => 'pending',
        ]);
        $this->assertDatabaseHas('monthly_dues', [
            'student_id'   => $student->id,
            'class_id'     => $class2->id,
            'period_year'  => now()->year,
            'period_month' => now()->month,
        ]);
    }

    public function test_skips_existing_dues_for_same_period(): void
    {
        $enrollment = ClassEnrollment::factory()->create();

        MonthlyDue::create([
            'student_id'   => $enrollment->student_id,
            'class_id'     => $enrollment->class_id,
            'enrollment_id' => $enrollment->id,
            'period_year'  => now()->year,
            'period_month' => now()->month,
            'amount'       => 40.00,
            'status'       => 'pending',
        ]);

        $this->artisan('dues:generate')
            ->assertSuccessful();

        $this->assertDatabaseCount('monthly_dues', 1);
    }

    public function test_ignores_withdrawn_enrollments(): void
    {
        ClassEnrollment::factory()->withdrawn()->create();

        $this->artisan('dues:generate')
            ->assertSuccessful();

        $this->assertDatabaseCount('monthly_dues', 0);
    }

    public function test_creates_dues_even_when_student_is_not_member(): void
    {
        // Student without any entitlements (is_member = false)
        $enrollment = ClassEnrollment::factory()->create();

        $student = $enrollment->student;
        $this->assertFalse($student->is_member);

        $this->artisan('dues:generate')
            ->assertSuccessful();

        $this->assertDatabaseCount('monthly_dues', 1);
        $this->assertDatabaseHas('monthly_dues', [
            'student_id' => $student->id,
        ]);
    }

    public function test_accepts_custom_year_month_and_amount(): void
    {
        ClassEnrollment::factory()->create();

        $this->artisan('dues:generate', [
            '--year'   => 2026,
            '--month'  => 3,
            '--amount' => 55.00,
        ])->assertSuccessful();

        $this->assertDatabaseHas('monthly_dues', [
            'period_year'    => 2026,
            'period_month'   => 3,
            'amount'         => '55.00',
            'price_override' => true,
        ]);
    }

    public function test_handles_no_enrollments_gracefully(): void
    {
        $this->artisan('dues:generate')
            ->assertSuccessful();

        $this->assertDatabaseCount('monthly_dues', 0);
    }

    public function test_is_idempotent(): void
    {
        ClassEnrollment::factory()->create();

        $this->artisan('dues:generate')->assertSuccessful();
        $this->artisan('dues:generate')->assertSuccessful();
        $this->artisan('dues:generate')->assertSuccessful();

        $this->assertDatabaseCount('monthly_dues', 1);
    }

    // ── New pricing tests ────────────────────────────────────────────

    public function test_uses_class_monthly_price(): void
    {
        $class = CourseClass::factory()->priced(60.00)->create();

        ClassEnrollment::factory()->create(['class_id' => $class->id]);

        $this->artisan('dues:generate')->assertSuccessful();

        $this->assertDatabaseHas('monthly_dues', [
            'class_id'   => $class->id,
            'amount'     => '60.00',
            'base_price' => '60.00',
        ]);
    }

    public function test_applies_percent_discount(): void
    {
        $class = CourseClass::factory()->priced(50.00)->create();

        ClassEnrollment::factory()
            ->withDiscount(percent: 20)
            ->create(['class_id' => $class->id]);

        $this->artisan('dues:generate')->assertSuccessful();

        $this->assertDatabaseHas('monthly_dues', [
            'class_id'         => $class->id,
            'amount'           => '40.00',
            'base_price'       => '50.00',
            'discount_applied' => '10.00',
            'price_override'   => false,
        ]);
    }

    public function test_applies_fixed_discount(): void
    {
        $class = CourseClass::factory()->priced(50.00)->create();

        ClassEnrollment::factory()
            ->withDiscount(amount: 10)
            ->create(['class_id' => $class->id]);

        $this->artisan('dues:generate')->assertSuccessful();

        $this->assertDatabaseHas('monthly_dues', [
            'class_id'         => $class->id,
            'amount'           => '40.00',
            'base_price'       => '50.00',
            'discount_applied' => '10.00',
        ]);
    }

    public function test_applies_both_discounts(): void
    {
        $class = CourseClass::factory()->priced(100.00)->create();

        ClassEnrollment::factory()
            ->withDiscount(percent: 10, amount: 5)
            ->create(['class_id' => $class->id]);

        $this->artisan('dues:generate')->assertSuccessful();

        // 100 * 0.90 - 5 = 85
        $this->assertDatabaseHas('monthly_dues', [
            'class_id'         => $class->id,
            'amount'           => '85.00',
            'base_price'       => '100.00',
            'discount_applied' => '15.00',
        ]);
    }

    public function test_discount_cannot_make_amount_negative(): void
    {
        $class = CourseClass::factory()->priced(10.00)->create();

        ClassEnrollment::factory()
            ->withDiscount(amount: 50)
            ->create(['class_id' => $class->id]);

        $this->artisan('dues:generate')->assertSuccessful();

        $this->assertDatabaseHas('monthly_dues', [
            'class_id'   => $class->id,
            'amount'     => '0.00',
            'base_price' => '10.00',
        ]);
    }

    public function test_amount_flag_overrides_everything(): void
    {
        $class = CourseClass::factory()->priced(60.00)->create();

        ClassEnrollment::factory()
            ->withDiscount(percent: 10)
            ->create(['class_id' => $class->id]);

        $this->artisan('dues:generate', ['--amount' => 35.00])
            ->assertSuccessful();

        $this->assertDatabaseHas('monthly_dues', [
            'class_id'       => $class->id,
            'amount'         => '35.00',
            'base_price'     => null,
            'price_override' => true,
        ]);
    }

    public function test_price_change_affects_future_months(): void
    {
        $class   = CourseClass::factory()->priced(40.00)->create();
        $student = Student::factory()->create();

        ClassEnrollment::factory()->create([
            'student_id' => $student->id,
            'class_id'   => $class->id,
        ]);

        // Generate month 1
        $this->artisan('dues:generate', ['--year' => 2026, '--month' => 1])
            ->assertSuccessful();

        // Change price
        $class->update(['monthly_price' => 60.00]);

        // Generate month 2
        $this->artisan('dues:generate', ['--year' => 2026, '--month' => 2])
            ->assertSuccessful();

        $this->assertDatabaseHas('monthly_dues', [
            'class_id'     => $class->id,
            'period_month' => 1,
            'amount'       => '40.00',
            'base_price'   => '40.00',
        ]);
        $this->assertDatabaseHas('monthly_dues', [
            'class_id'     => $class->id,
            'period_month' => 2,
            'amount'       => '60.00',
            'base_price'   => '60.00',
        ]);
    }

    public function test_different_classes_different_prices(): void
    {
        $student = Student::factory()->create();
        $class1  = CourseClass::factory()->priced(30.00)->create();
        $class2  = CourseClass::factory()->priced(70.00)->create();

        ClassEnrollment::factory()->create([
            'student_id' => $student->id,
            'class_id'   => $class1->id,
        ]);
        ClassEnrollment::factory()->create([
            'student_id' => $student->id,
            'class_id'   => $class2->id,
        ]);

        $this->artisan('dues:generate')->assertSuccessful();

        $this->assertDatabaseHas('monthly_dues', [
            'class_id'   => $class1->id,
            'amount'     => '30.00',
            'base_price' => '30.00',
        ]);
        $this->assertDatabaseHas('monthly_dues', [
            'class_id'   => $class2->id,
            'amount'     => '70.00',
            'base_price' => '70.00',
        ]);
    }
}
