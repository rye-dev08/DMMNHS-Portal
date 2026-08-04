<?php

namespace App\Http\Requests;

use App\Rules\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'string', new PasswordPolicy],
            'role' => ['required', 'in:admin,teacher,student'],

            // Student profile fields (only used when role == student).
            'sex' => ['nullable', 'in:M,F'],
            'birthday' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:1'],
            'grade_level' => ['nullable', 'integer', 'min:1'],

            // Teacher profile fields (only used when role == teacher).
            'advisory_class' => ['nullable', 'string', 'max:100'],
            'max_students' => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_subjects' => ['nullable', 'integer', 'min:1', 'max:30'],
        ];
    }
}