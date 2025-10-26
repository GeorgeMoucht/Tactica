<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'    => ['required','string','max:100'],
            'last_name'     => ['required','string','max:100'],
            'birthdate'     => ['nullable','date_format:Y-m-d'],
            'email'         => ['nullable','email','max:255'],
            'phone'         => ['nullable','string','max:50'],
            'level'         => ['nullable','in:beginner,intermediate,advanced'],
            'interests'     => ['nullable','array'],
            'interests.*'   => ['in:painting,ceramics,drawing'],
            'notes'         => ['nullable','string'],
            'medical_note'  => ['nullable','string'],
            'consent_media' => ['required','boolean'],

            'address'       => ['nullable','array'],
            'address.street'=> ['nullable','string','max:255'],
            'address.city'  => ['nullable','string','max:255'],
            'address.zip'   => ['nullable','string','max:20'],
        ];
    }
}