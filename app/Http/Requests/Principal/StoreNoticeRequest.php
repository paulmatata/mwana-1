<?php

namespace App\Http\Requests\Principal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPrincipal() ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()->school_id;

        return [
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:2000'],
            // Both nullable - leaving both blank means "school-wide", which only
            // a principal is allowed to do (teachers must target a class or student).
            'school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_id', $schoolId)],
            'student_id' => ['nullable', Rule::exists('students', 'id')->where('school_id', $schoolId)],
        ];
    }
}
