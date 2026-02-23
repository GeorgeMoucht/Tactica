<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceHistorySessionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'session_id'        => $this->id,
            'date'              => $this->date->toDateString(),
            'starts_time'       => $this->starts_time,
            'ends_time'         => $this->ends_time,
            'conducted_by_name' => $this->conductor?->name,
            'present_count'     => $this->attendances?->where('status', 'present')->count() ?? 0,
            'absent_count'      => $this->attendances?->where('status', 'absent')->count() ?? 0,
            'attendances'       => $this->attendances?->map(fn ($a) => [
                'student_id'   => $a->student_id,
                'student_name' => $a->student?->name,
                'status'       => $a->status,
            ])->values()->all() ?? [],
        ];
    }
}
