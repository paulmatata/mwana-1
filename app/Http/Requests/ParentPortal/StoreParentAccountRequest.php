<?php

namespace App\Http\Requests\ParentPortal;

use Illuminate\Foundation\Http\FormRequest;

class StoreParentAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // guest-only in practice - controller redirects already-authenticated users away
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone', 'required_without:email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Provide at least an email or a phone number to log in with next time.',
            'phone.required_without' => 'Provide at least an email or a phone number to log in with next time.',
        ];
    }
}
