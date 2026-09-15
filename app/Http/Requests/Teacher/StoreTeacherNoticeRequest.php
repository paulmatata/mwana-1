<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()->school_id;

        return [
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:2000'],
            // A teacher must pick exactly one of these - school-wide notices are
            // reserved for the principal, so neither is allowed to be left blank.
            'school_class_id' => ['nullable', 'required_without:student_id', Rule::exists('school_classes', 'id')->where('school_id', $schoolId)],
            'student_id' => ['nullable', 'required_without:school_class_id', Rule::exists('students', 'id')->where('school_id', $schoolId)],
        ];
    }

    public function messages(): array
    {
        return [
            'school_class_id.required_without' => 'Choose a class or a specific student for this notice.',
            'student_id.required_without' => 'Choose a class or a specific student for this notice.',
        ];
    }
}
