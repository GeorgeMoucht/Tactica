<?php

namespace App\Http\Requests\Guardian;

use Illuminate\Foundation\Http\FormRequest;

class IndexGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query'     => ['sometimes', 'string', 'max:255'],
            'pageSize'  => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}