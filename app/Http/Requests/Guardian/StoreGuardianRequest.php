<?php

namespace App\Http\Requests\Guardian;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],

            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],

            'preferred_contact' => ['nullable', 'in:email,phone,sms'],
            'notes'             => ['nullable', 'string'],
            'newsletter_consent'=> ['boolean'],

            'address'        => ['nullable', 'array'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.city'   => ['nullable', 'string', 'max:255'],
            'address.zip'    => ['nullable', 'string', 'max:20'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalize checkbox-like inputs
        if ($this->has('newsletter_consent')) {
            $this->merge([
                'newsletter_consent' => filter_var($this->input('newsletter_consent'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}