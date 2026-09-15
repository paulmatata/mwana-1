<?php

namespace App\Http\Requests\Principal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPrincipal() ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()->school_id;

        return [
            'whole_school' => ['nullable', 'boolean'],
            'school_class_ids' => ['required_if:whole_school,false', 'array'],
            'school_class_ids.*' => [Rule::exists('school_classes', 'id')->where('school_id', $schoolId)],
            'name' => ['required', 'string', 'max:150'],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $schoolId)],
            'exam_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'school_class_ids.required_if' => 'Select at least one class, or choose "Whole school".',
        ];
    }
}
