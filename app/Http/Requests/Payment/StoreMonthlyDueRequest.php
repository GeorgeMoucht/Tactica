<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonthlyDueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id'      => ['required', 'integer', 'exists:classes,id'],
            'enrollment_id' => ['nullable', 'integer', 'exists:class_enrollments,id'],
            'period_year'   => ['required', 'integer', 'min:2020', 'max:2100'],
            'period_month'  => ['required', 'integer', 'min:1', 'max:12'],
            'amount'        => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'period_year.min'  => 'The period year must be at least 2020.',
            'period_month.min' => 'The period month must be between 1 and 12.',
            'period_month.max' => 'The period month must be between 1 and 12.',
        ];
    }
}
