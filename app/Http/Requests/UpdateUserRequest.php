<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],

            // Student profile fields (used when role == student).
            'sex' => ['nullable', 'in:M,F'],
            'birthday' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:1'],
            'grade_level' => ['nullable', 'integer', 'min:1'],

            // Teacher profile fields (used when role == teacher).
            'advisory_class' => ['nullable', 'string', 'max:50'],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'max_subjects' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
