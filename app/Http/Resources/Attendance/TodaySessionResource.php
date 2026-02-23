<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Resources\Json\JsonResource;

class TodaySessionResource extends JsonResource
{
    public function toArray($request): array
    {
        $courseClass = $this->courseClass;

        return [
            'id'                => $this->id,
            'class_id'          => $this->class_id,
            'date'              => $this->date->toDateString(),
            'starts_time'       => $this->starts_time,
            'ends_time'         => $this->ends_time,
            'class_title'       => $courseClass?->title,
            'teacher_name'      => $courseClass?->teacher?->name,
            'conducted_by_name' => $this->conductor?->name,
            'enrolled_count'    => $courseClass?->activeEnrollments?->count() ?? 0,
            'attended_count'    => $this->attendances?->where('status', 'present')->count() ?? 0,
        ];
    }
}
