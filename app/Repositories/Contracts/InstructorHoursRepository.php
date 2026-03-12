<?php

namespace App\Repositories\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;

interface InstructorHoursRepository
{
    /**
     * Get aggregated instructor hours for a date range.
     *
     * Each row: instructor_id, instructor_name, total_hours, session_count, classes_taught (comma-separated)
     */
    public function getHoursByPeriod(Carbon $from, Carbon $to): Collection;
}
