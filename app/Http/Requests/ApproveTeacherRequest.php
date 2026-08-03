<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'max_students' => ['required', 'integer', 'min:1'],
            'max_subjects' => ['required', 'integer', 'min:1'],
            'advisory_class' => ['nullable', 'string', 'max:50'],
        ];
    }
}