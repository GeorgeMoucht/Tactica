<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\InstructorHoursRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentInstructorHoursRepository implements InstructorHoursRepository
{
    public function getHoursByPeriod(Carbon $from, Carbon $to): Collection
    {
        return DB::table('class_sessions as cs')
            ->join('classes as c', 'cs.class_id', '=', 'c.id')
            ->join('users as u', 'u.id', '=', DB::raw('COALESCE(cs.conducted_by, c.teacher_id)'))
            ->whereBetween('cs.date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('u.id', 'u.name')
            ->select([
                'u.id as instructor_id',
                'u.name as instructor_name',
                DB::raw('ROUND(SUM(TIMESTAMPDIFF(MINUTE, cs.starts_time, cs.ends_time)) / 60, 1) as total_hours'),
                DB::raw('COUNT(cs.id) as session_count'),
                DB::raw('GROUP_CONCAT(DISTINCT c.title SEPARATOR ", ") as classes_taught'),
            ])
            ->orderByDesc('total_hours')
            ->get();
    }
}
