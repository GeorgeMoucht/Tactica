<?php

namespace App\Http\Requests\Registration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; 

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // protected by auth:api already;
    }

    public function rules(): array
    {
        $contact = [
            'email',
            'sms',
            'phone'
        ];

        $levels = [
            'beginner',
            'intermediate',
            'advanced'
        ];

        $interests = [
            'painting',
            'ceramics',
            'drawing'
        ];

        return [
            // Guardian
            'guardian.first_name'           => ['required','string','max:120'],
            'guardian.last_name'            => ['required','string','max:120'],
            'guardian.email'                => ['nullable','email','max:180'],
            'guardian.phone'                => ['nullable','string','max:60'],
            'guardian.address'              => ['nullable','array'],
            'guardian.address.street'       => ['nullable','string','max:180'],
            'guardian.address.city'         => ['nullable','string','max:120'],
            'guardian.address.zip'          => ['nullable','string','max:20'],
            'guardian.preferred_contact'    => ['nullable', Rule::in($contact)],
            'guardian.notes'                => ['nullable','string'],
            'guardian.newsletter_consent'   => ['boolean'],

            // Student
            'students'                  => ['required','array','min:1'],
            'students.*.first_name'     => ['required','string','max:120'],
            'students.*.last_name'      => ['required','string','max:120'],
            'students.*.birthdate'      => ['required','date','before_or_equal:today'],
            'students.*.email'          => ['nullable','email','max:180'],
            'students.*.phone'          => ['nullable','string','max:60'],
            'students.*.address'        => ['nullable','array'],
            'students.*.address.street' => ['nullable','string','max:180'],
            'students.*.address.city'   => ['nullable','string','max:120'],
            'students.*.address.zip'    => ['nullable','string','max:20'],
            'students.*.level'          => ['nullable', Rule::in($levels)],
            'students.*.interests'      => ['nullable','array'],
            'students.*.interests.*'    => [Rule::in($interests)],
            'students.*.notes'          => ['nullable','string'],
            'students.*.medical_note'   => ['nullable','string'],
            'students.*.consent_media'  => ['boolean'],
        ];
    }
}