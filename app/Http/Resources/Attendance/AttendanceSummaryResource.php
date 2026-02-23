<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        $class    = $this->resource['class'];
        $students = $this->resource['students'];

        return [
            'class_id'       => $class->id,
            'class_title'    => $class->title,
            'total_sessions' => $this->resource['total_sessions'],
            'students'       => $students->map(fn ($s) => [
                'student_id'      => $s->student_id,
                'student_name'    => trim($s->first_name . ' ' . $s->last_name),
                'total_present'   => (int) $s->total_present,
                'total_absent'    => (int) $s->total_absent,
                'attendance_rate' => $s->total_sessions > 0
                    ? round(($s->total_present / $s->total_sessions) * 100, 2)
                    : 0,
            ])->all(),
        ];
    }
}
