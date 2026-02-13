<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'discount_percent' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'discount_amount'  => ['sometimes', 'numeric', 'min:0', 'max:9999.99'],
            'discount_note'    => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
