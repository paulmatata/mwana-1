<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StorePrincipalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone', 'required_without:email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Provide at least an email or a phone number for the principal to log in with.',
            'phone.required_without' => 'Provide at least an email or a phone number for the principal to log in with.',
        ];
    }
}
