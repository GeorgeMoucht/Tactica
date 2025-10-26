<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // already behind auth middleware; tighten if needed
    }

    public function rules(): array
    {
        return [
            'query'    => ['nullable', 'string', 'max:120'],
            'page'     => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Optional filters you might enable later
            'level'    => ['nullable', Rule::in(['beginner','intermediate','advanced'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'query'    => $this->has('query') ? trim((string) $this->input('query')) : null,
            'page'     => $this->filled('page') ? (int) $this->input('page') : null,
            'pageSize' => $this->filled('pageSize') ? (int) $this->input('pageSize') : null,
        ]);
    }
}
