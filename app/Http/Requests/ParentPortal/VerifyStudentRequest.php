<?php

namespace App\Http\Requests\ParentPortal;

use Illuminate\Foundation\Http\FormRequest;

class VerifyStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access already gated in the controller (guest or parent role only)
    }

    public function rules(): array
    {
        return [
            'admission_no' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
