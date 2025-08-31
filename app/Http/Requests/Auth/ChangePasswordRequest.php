<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'current_password'  => ['required', 'string'],
            'new_password'      => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function currentPassword(): string { return (string) $this->input('current_password'); }
    public function newPassword(): string     { return (string) $this->input('new_passwor '); }
}