<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

class MonthlyDueResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'student_id'   => $this->student_id,
            'class_id'     => $this->class_id,
            'enrollment_id' => $this->enrollment_id,
            'period_year'  => $this->period_year,
            'period_month' => $this->period_month,
            'period_label' => $this->period_label,
            'amount'           => (float) $this->amount,
            'base_price'       => $this->base_price !== null ? (float) $this->base_price : null,
            'discount_applied' => $this->discount_applied !== null ? (float) $this->discount_applied : null,
            'price_override'   => (bool) $this->price_override,
            'status'       => $this->status,
            'paid_at'      => $this->paid_at?->toIso8601String(),
            'notes'        => $this->notes,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),

            'class' => $this->whenLoaded('courseClass', fn () => [
                'id'    => $this->courseClass->id,
                'title' => $this->courseClass->title,
            ]),

            'student' => $this->whenLoaded('student', fn () => [
                'id'   => $this->student->id,
                'name' => $this->student->name,
            ]),
        ];
    }
}
