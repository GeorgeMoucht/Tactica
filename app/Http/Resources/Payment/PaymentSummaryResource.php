<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $data = $this->resource;

        return [
            'student_id'        => $data['student_id'],
            'total_paid'        => (float) $data['total_paid'],
            'total_outstanding' => (float) $data['total_outstanding'],

            'registration' => $this->getRegistrationStatus($data['student_id']),

            'outstanding_dues' => $data['outstanding_dues']->map(fn ($due) => [
                'id'     => $due->id,
                'period' => $due->period_label,
                'amount' => (float) $due->amount,
                'class'  => [
                    'id'    => $due->courseClass->id ?? null,
                    'title' => $due->courseClass->title ?? null,
                ],
            ])->values()->all(),
        ];
    }

    private function getRegistrationStatus(int $studentId): ?array
    {
        $student = \App\Models\Student::with(['entitlements.product'])->find($studentId);

        if (!$student) {
            return null;
        }

        $registration = $student->entitlements
            ->first(fn ($e) => $e->product?->type === 'registration');

        if (!$registration) {
            return [
                'status'     => 'inactive',
                'expires_at' => null,
            ];
        }

        $isActive = $registration->starts_at <= today() && $registration->ends_at >= today();

        return [
            'status'     => $isActive ? 'active' : 'expired',
            'expires_at' => $registration->ends_at?->toDateString(),
        ];
    }
}
