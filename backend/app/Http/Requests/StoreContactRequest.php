<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'city' => ['nullable', 'string', 'max:255'],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.max' => 'First name cannot exceed 255 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.max' => 'Last name cannot exceed 255 characters.',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.max' => 'Phone number cannot exceed 30 characters.',
            'birthdate.date' => 'Please provide a valid date.',
            'birthdate.before' => 'Birthdate must be before today.',
            'department_ids.array' => 'Departments must be an array.',
            'department_ids.*.exists' => 'One or more selected departments do not exist.',
        ];
    }
}
