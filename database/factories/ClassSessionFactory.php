<?php

namespace Database\Factories;

use App\Models\ClassSession;
use App\Models\CourseClass;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassSessionFactory extends Factory
{
    protected $model = ClassSession::class;

    public function definition(): array
    {
        return [
            'class_id'    => CourseClass::factory(),
            'date'        => Carbon::today()->toDateString(),
            'starts_time' => '10:00',
            'ends_time'   => '11:00',
        ];
    }

    public function forDate(Carbon $date): static
    {
        return $this->state(['date' => $date->toDateString()]);
    }
}
