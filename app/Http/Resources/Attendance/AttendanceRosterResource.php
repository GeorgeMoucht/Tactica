<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRosterResource extends JsonResource
{
    public function toArray($request): array
    {
        $session = $this->resource['session'];

        return [
            'session' => [
                'id'                => $session->id,
                'date'              => $session->date->toDateString(),
                'class_title'       => $session->courseClass?->title,
                'teacher_name'      => $session->courseClass?->teacher?->name,
                'teacher_id'        => $session->courseClass?->teacher_id,
                'conducted_by'      => $session->conducted_by,
                'conducted_by_name' => $session->conductor?->name,
            ],
            'teachers' => $this->resource['teachers'],
            'debt_summary' => collect($this->resource['debt_summary'])->map(fn ($d) => [
                'student_id'         => $d['student_id'],
                'name'               => $d['name'],
                'outstanding_amount' => (float) $d['outstanding_amount'],
            ])->all(),
            'students' => collect($this->resource['students'])->map(fn ($s) => [
                'student_id'         => $s['student']->id,
                'name'               => $s['student']->name,
                'attendance_status'  => $s['attendance_status'],
                'has_debt'           => $s['has_debt'],
                'outstanding_amount' => (float) $s['outstanding_amount'],
            ])->all(),
        ];
    }
}
