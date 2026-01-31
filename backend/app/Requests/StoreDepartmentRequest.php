<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255',Rule::unique('departments', 'name')->whereNull('deleted_at'),],
            'description' => ['nullable', 'string', 'max:1000'],,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Department name is required.',
            'name.max' => 'Department name cannot exceed 255 characters.',
            'name.unique' => 'A department with this name already exists.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ];
    }
}
