<?php

namespace App\Http\Requests\Principal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPrincipal() ?? false;
    }

    public function rules(): array
    {
        $class = $this->route('class');

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('school_classes')
                    ->where(fn ($q) => $q->where('school_id', $class->school_id)->where('stream', $this->input('stream')))
                    ->ignore($class->id),
            ],
            'stream' => ['nullable', 'string', 'max:100'],
            'class_teacher_id' => ['nullable', 'exists:users,id'],
            'promotes_to_class_id' => [
                'nullable',
                Rule::exists('school_classes', 'id')->where('school_id', $class->school_id),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $class = $this->route('class');
            if ($this->input('promotes_to_class_id') && (int) $this->input('promotes_to_class_id') === $class->id) {
                $validator->errors()->add('promotes_to_class_id', 'A class can\'t promote into itself.');
            }
        });
    }
}
