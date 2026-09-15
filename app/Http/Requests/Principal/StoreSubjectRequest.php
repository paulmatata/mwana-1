<?php

namespace App\Http\Requests\Principal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPrincipal() ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()->school_id;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('subjects')->where(fn ($q) => $q->where('school_id', $schoolId))],
            'code' => ['nullable', 'string', 'max:20'],
            'is_core' => ['nullable', 'boolean'],
        ];
    }
}
