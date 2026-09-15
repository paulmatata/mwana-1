<?php

namespace App\Http\Requests\Principal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPrincipal() ?? false;
    }

    public function rules(): array
    {
        $subject = $this->route('subject');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('subjects')->where(fn ($q) => $q->where('school_id', $subject->school_id))->ignore($subject->id)],
            'code' => ['nullable', 'string', 'max:20'],
            'is_core' => ['nullable', 'boolean'],
        ];
    }
}
