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
            'query'       => ['nullable', 'string', 'max:180'],
            'pageSize'    => ['nullable', 'integer', 'min:1', 'max:200'],
            'type'        => ['nullable', 'string', 'in:weekly,workshop'],
            'active'      => ['nullable'],
            'day_of_week' => ['nullable', 'integer', 'min:1', 'max:7'],
            'teacher_id'  => ['nullable', 'integer', 'exists:users,id'],
            'sort_by'     => ['nullable', 'string', 'in:id,title,type,active,day_of_week,capacity,created_at,teacher.name'],
            'sort_order'  => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
