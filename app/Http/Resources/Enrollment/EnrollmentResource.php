<?php

namespace App\Http\Resources\Enrollment;

use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'student_id'   => $this->student_id,
            'class_id'     => $this->class_id,
            'status'       => $this->status,
            'enrolled_at'  => $this->enrolled_at?->toDateString(),
            'withdrawn_at' => $this->withdrawn_at?->toDateString(),
            'notes'            => $this->notes,
            'discount_percent' => (float) $this->discount_percent,
            'discount_amount'  => (float) $this->discount_amount,
            'discount_note'    => $this->discount_note,
            'effective_price'  => $this->effective_price,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),

            'student' => $this->whenLoaded('student', function () {
                $student = $this->student;
                $guardian = $student->relationLoaded('guardians')
                    ? $student->guardians->first()
                    : null;

                // Use student's contact info, fallback to guardian's
                $email = $student->email ?: ($guardian?->email ?? null);
                $phone = $student->phone ?: ($guardian?->phone ?? null);

                return [
                    'id'         => $student->id,
                    'name'       => trim($student->first_name . ' ' . $student->last_name),
                    'first_name' => $student->first_name,
                    'last_name'  => $student->last_name,
                    'email'      => $email,
                    'phone'      => $phone,
                ];
            }),

            'course_class' => $this->whenLoaded('courseClass', fn () => [
                'id'          => $this->courseClass->id,
                'title'       => $this->courseClass->title,
                'type'        => $this->courseClass->type,
                'active'      => $this->courseClass->active,
                'day_of_week' => $this->courseClass->day_of_week,
                'starts_time' => $this->courseClass->starts_time,
                'ends_time'   => $this->courseClass->ends_time,
                'capacity'    => $this->courseClass->capacity,
                'teacher'     => $this->courseClass->relationLoaded('teacher') && $this->courseClass->teacher
                    ? [
                        'id'   => $this->courseClass->teacher->id,
                        'name' => $this->courseClass->teacher->name,
                    ]
                    : null,
            ]),
        ];
    }
}
