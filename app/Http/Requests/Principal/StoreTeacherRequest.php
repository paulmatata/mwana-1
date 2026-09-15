<?php

namespace App\Http\Requests\Principal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPrincipal() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone', 'required_without:email'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => [Rule::exists('subjects', 'id')->where('school_id', $this->user()->school_id)],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Provide at least an email or a phone number for the teacher to log in with.',
            'phone.required_without' => 'Provide at least an email or a phone number for the teacher to log in with.',
        ];
    }
}
