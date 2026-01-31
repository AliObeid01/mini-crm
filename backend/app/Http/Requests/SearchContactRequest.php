<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'department_id.exists' => 'The selected department does not exist.',
        ];
    }

}
