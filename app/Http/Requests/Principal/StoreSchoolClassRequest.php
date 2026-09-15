<?php

namespace App\Http\Requests\Principal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPrincipal() ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()->school_id;

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('school_classes')->where(fn ($q) => $q->where('school_id', $schoolId)->where('stream', $this->input('stream'))),
            ],
            'stream' => ['nullable', 'string', 'max:100'],
            'class_teacher_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
