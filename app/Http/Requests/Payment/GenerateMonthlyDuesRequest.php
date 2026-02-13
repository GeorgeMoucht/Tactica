<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class GenerateMonthlyDuesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year'   => ['required', 'integer', 'min:2020', 'max:2100'],
            'month'  => ['required', 'integer', 'min:1', 'max:12'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'year.required'  => 'The year is required.',
            'month.required' => 'The month is required.',
            'month.min'      => 'The month must be between 1 and 12.',
            'month.max'      => 'The month must be between 1 and 12.',
        ];
    }
}
