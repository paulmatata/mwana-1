<?php

namespace App\Http\Requests\Principal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPrincipal() ?? false;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher->id), 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($teacher->id), 'required_without:email'],
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
