<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conducted_by'             => ['required', 'integer', 'exists:users,id'],
            'attendances'              => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'attendances.*.status'     => ['required', 'in:present,absent'],
            'attendances.*.notes'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
