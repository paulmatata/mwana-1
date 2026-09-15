<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'admission_no' => ['required', 'string', 'max:50', Rule::unique('students')->where(fn ($q) => $q->where('school_id', $student->school_id))->ignore($student->id)],
            'school_class_id' => ['required', Rule::exists('school_classes', 'id')->where('school_id', $student->school_id)],
            'gender' => ['nullable', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,transferred,graduated,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'admission_no.unique' => 'A student with this admission number already exists at this school.',
        ];
    }
}
