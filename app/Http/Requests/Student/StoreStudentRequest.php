<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Arr;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $levels    = ['beginner','intermediate','advanced'];
        $interests = ['painting','ceramics','drawing'];
        $contacts  = ['email','sms','phone'];
        $relations = ['father', 'mother', 'guardian', 'other'];

        return [
            // Student (required)
            'student'                       => ['required','array'],
            'student.first_name'            => ['required','string','max:120'],
            'student.last_name'             => ['required','string','max:120'],
            'student.birthdate'             => ['required','date','before_or_equal:today'],

            'student.email'                 => ['nullable','email','max:180'],
            'student.phone'                 => ['nullable','string','max:60'],
            'student.preferred_contact'     => ['nullable', Rule::in($contacts)],
            'student.contact_notes'         => ['nullable','string'],

            'student.is_member'             => ['boolean'],
            'student.registration_date'     => ['nullable', 'date'],

            'student.address'               => ['nullable','array'],
            'student.address.street'        => ['nullable','string','max:180'],
            'student.address.city'          => ['nullable','string','max:120'],
            'student.address.zip'           => ['nullable','string','max:20'],

            'student.level'                 => ['nullable', Rule::in($levels)],
            'student.interests'             => ['nullable','array'],
            'student.interests.*'           => [Rule::in($interests)],

            'student.notes'                 => ['nullable','string'],
            'student.medical_note'          => ['nullable','string'],
            'student.consent_media'         => ['boolean'],

            // Guardians (optional unless minor)
            'guardians'                     => ['nullable','array'],
            'guardians.*.first_name'        => ['required_with:guardians','string','max:120'],
            'guardians.*.last_name'         => ['required_with:guardians','string','max:120'],
            'guardians.*.email'             => ['nullable','email','max:180'],
            'guardians.*.phone'             => ['nullable','string','max:60'],
            'guardians.*.address'           => ['nullable','array'],
            'guardians.*.address.street'    => ['nullable','string','max:180'],
            'guardians.*.address.city'      => ['nullable','string','max:120'],
            'guardians.*.address.zip'       => ['nullable','string','max:20'],
            'guardians.*.preferred_contact' => ['nullable', Rule::in($contacts)],
            'guardians.*.notes'             => ['nullable','string'],
            'guardians.*.newsletter_consent'=> ['boolean'],
            'guardians.*.relation'          => ['nullable', Rule::in($relations)]
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $data = $this->input('student');

            // Minor must hae guardian
            $birth = $data['birthdate'] ?? null;
            if ($birth) {
                $isMinor = Carbon::parse($birth)->diffInYears(now()) < 18;
                if ($isMinor && empty($this->input('guardians', []))) {
                    $v->errors()->add('guardians', 'At least one guardian is required for a minor.');   
                }
            }

            // if is_member === true then registration_date is required
            if (!empty($data['is_member']) && empty($data['registration_date'])) {
                $v->errors()->add('student.registration_date', 'Registration date is required for members.');
            }
        });
        // $validator->after(function (Validator $v) {
        //     $birth = Arr::get($this->all(), 'student.birthdate');

        //     if ($birth) {
        //         $isMinor = Carbon::parse($birth)->diffInYears(now()) < 18;
        //         if ($isMinor && empty($this->input('guardians', []))) {
        //             $v->errors()->add('guardians', 'At least one guardian is required for a minor.');
        //         }
        //     }
        // });
    }
}
