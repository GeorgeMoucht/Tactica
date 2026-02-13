<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'first_name'    => fake()->firstName(),
            'last_name'     => fake()->lastName(),
            'birthdate'     => fake()->date('Y-m-d', '-6 years'),
            'email'         => fake()->unique()->safeEmail(),
            'phone'         => fake()->phoneNumber(),
            'consent_media' => false,
        ];
    }
}
