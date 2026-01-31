<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department');
        
        return [
            'name' => ['sometimes','required','string','max:255',Rule::unique('departments', 'name')->ignore($departmentId)->whereNull('deleted_at'),],
            'description' => ['nullable', 'string', 'max:1000'],
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
