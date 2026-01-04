<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'starts_at' => ['required', 'date'],
            'ends_at'   => ['required', 'date', 'after:starts_at'],
            'paid_at'   => ['nullable', 'date']
        ];
    }
}