<?php

namespace App\Http\Requests\Principal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPrincipal() ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()->school_id;

        return [
            'name' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'make_current' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $exists = \App\Models\Term::where('school_id', $this->user()->school_id)
                ->where('name', $this->input('name'))
                ->where('year', $this->input('year'))
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', 'This term already exists for that year.');
            }
        });
    }
}
