<?php

namespace App\Services\Instructor;

use App\Repositories\Contracts\InstructorHoursRepository;
use Carbon\Carbon;

class InstructorHoursService
{
    public function __construct(
        private readonly InstructorHoursRepository $repo
    ) {}

    /**
     * Get instructor hours grouped by instructor for a given period.
     */
    public function getHoursByPeriod(Carbon $from, Carbon $to): array
    {
        return $this->repo->getHoursByPeriod($from, $to)
            ->map(fn ($r) => [
                'instructor_id'   => (int) $r->instructor_id,
                'instructor_name' => $r->instructor_name,
                'intructor_name'  => $r->instructor_name, // typo compat
                'hours'           => (float) $r->total_hours, // dashboard widget compat
                'total_hours'     => (float) $r->total_hours,
                'session_count'   => (int) $r->session_count,
                'classes_taught'  => array_filter(explode(', ', $r->classes_taught ?? '')),
            ])
            ->toArray();
    }
}
