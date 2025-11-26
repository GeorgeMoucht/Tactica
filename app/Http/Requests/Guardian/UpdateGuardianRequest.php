<?php

namespace App\Http\Requests\Guardian;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name'  => ['sometimes', 'string', 'max:255'],

            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],

            'preferred_contact' => ['sometimes', 'nullable', 'in:email,phone,sms'],
            'notes'             => ['sometimes', 'nullable', 'string'],
            'newsletter_consent'=> ['sometimes', 'boolean'],

            'address'        => ['sometimes', 'nullable', 'array'],
            'address.street' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address.city'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'address.zip'    => ['sometimes', 'nullable', 'string', 'max:20'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('newsletter_consent')) {
            $this->merge([
                'newsletter_consent' => filter_var($this->input('newsletter_consent'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}