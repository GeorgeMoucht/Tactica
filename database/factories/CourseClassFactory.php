<?php

namespace Database\Factories;

use App\Models\CourseClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseClassFactory extends Factory
{
    protected $model = CourseClass::class;

    public function definition(): array
    {
        return [
            'title'         => fake()->words(3, true),
            'type'          => 'weekly',
            'active'        => true,
            'day_of_week'   => fake()->numberBetween(1, 7),
            'starts_time'   => '10:00',
            'ends_time'     => '11:00',
            'capacity'      => 20,
            'monthly_price' => 40.00,
        ];
    }

    public function priced(float $price): static
    {
        return $this->state(['monthly_price' => $price]);
    }
}
