<?php

namespace Tests\Feature;

use App\Models\ClassEnrollment;
use App\Models\CourseClass;
use App\Models\Product;
use App\Models\Student;
use App\Models\StudentEntitlement;
use App\Services\Enrollment\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentDiscountTest extends TestCase
{
    use RefreshDatabase;

    private function createMemberStudent(): Student
    {
        $student = Student::factory()->create();

        $product = Product::create([
            'name'           => 'Registration',
            'type'           => 'registration',
            'price'          => 50.00,
            'billing_period' => 'year',
            'duration_days'  => 365,
        ]);

        StudentEntitlement::create([
            'student_id' => $student->id,
            'product_id' => $product->id,
            'starts_at'  => now()->subMonth(),
            'ends_at'    => now()->addYear(),
        ]);

        return $student;
    }

    public function test_can_enroll_with_discount(): void
    {
        $student = $this->createMemberStudent();
        $class   = CourseClass::factory()->priced(60.00)->create();

        /** @var EnrollmentService $service */
        $service = app(EnrollmentService::class);

        $enrollment = $service->enroll($student->id, $class->id, [
            'discount_percent' => 20,
            'discount_amount'  => 5,
            'discount_note'    => 'Sibling discount',
        ]);

        $this->assertEquals('active', $enrollment->status);
        $this->assertEquals('20.00', $enrollment->discount_percent);
        $this->assertEquals('5.00', $enrollment->discount_amount);
        $this->assertEquals('Sibling discount', $enrollment->discount_note);

        // Monthly due should reflect discounted price: 60 * 0.80 - 5 = 43
        $this->assertDatabaseHas('monthly_dues', [
            'student_id'       => $student->id,
            'class_id'         => $class->id,
            'amount'           => '43.00',
            'base_price'       => '60.00',
            'discount_applied' => '17.00',
            'price_override'   => false,
        ]);
    }

    public function test_can_update_enrollment_discount(): void
    {
        $student = $this->createMemberStudent();
        $class   = CourseClass::factory()->priced(50.00)->create();

        /** @var EnrollmentService $service */
        $service = app(EnrollmentService::class);

        $enrollment = $service->enroll($student->id, $class->id);

        $updated = $service->updateDiscount($enrollment->id, [
            'discount_percent' => 15,
            'discount_amount'  => 0,
            'discount_note'    => 'Loyalty discount',
        ]);

        $this->assertEquals('15.00', $updated->discount_percent);
        $this->assertEquals('0.00', $updated->discount_amount);
        $this->assertEquals('Loyalty discount', $updated->discount_note);
    }

    public function test_cannot_update_discount_on_withdrawn_enrollment(): void
    {
        $student = $this->createMemberStudent();
        $class   = CourseClass::factory()->create();

        /** @var EnrollmentService $service */
        $service = app(EnrollmentService::class);

        $enrollment = $service->enroll($student->id, $class->id);
        $service->withdraw($enrollment->id);

        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->expectExceptionMessage('Cannot update discount on a withdrawn enrollment.');

        $service->updateDiscount($enrollment->id, [
            'discount_percent' => 10,
        ]);
    }
}
