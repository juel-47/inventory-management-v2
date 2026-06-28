<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;

class RolesUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255', 'unique:roles,name,' . $this->route('id')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id']
        ];
    }
}
