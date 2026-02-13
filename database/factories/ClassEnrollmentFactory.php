<?php

namespace Database\Factories;

use App\Models\ClassEnrollment;
use App\Models\CourseClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassEnrollmentFactory extends Factory
{
    protected $model = ClassEnrollment::class;

    public function definition(): array
    {
        return [
            'student_id'       => Student::factory(),
            'class_id'         => CourseClass::factory(),
            'status'           => 'active',
            'enrolled_at'      => now(),
            'discount_percent' => 0.00,
            'discount_amount'  => 0.00,
        ];
    }

    public function withdrawn(): static
    {
        return $this->state([
            'status'       => 'withdrawn',
            'withdrawn_at' => now(),
        ]);
    }

    public function withDiscount(float $percent = 0, float $amount = 0, ?string $note = null): static
    {
        return $this->state([
            'discount_percent' => $percent,
            'discount_amount'  => $amount,
            'discount_note'    => $note,
        ]);
    }
}
