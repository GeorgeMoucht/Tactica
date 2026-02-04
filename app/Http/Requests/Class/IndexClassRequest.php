<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;

class IndexClassRequest extends FormRequest
{
    public function authorize(): bool {
        return true;
    }

    public function rules(): array
    {
        return [
            'query'    => ['nullable', 'string', 'max:180'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }
}