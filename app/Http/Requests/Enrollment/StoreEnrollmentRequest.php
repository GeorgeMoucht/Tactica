<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id'         => ['required', 'integer', 'exists:classes,id'],
            'enrolled_at'      => ['nullable', 'date'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount'  => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'discount_note'    => ['nullable', 'string', 'max:255'],
        ];
    }
}
