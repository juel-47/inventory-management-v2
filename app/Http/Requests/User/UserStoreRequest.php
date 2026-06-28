<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['nullable', 'image', 'max:2048'],
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'max:255'],
            'password' => ['required', 'min:8'],
            'status' => ['required', 'boolean'],
            'user_role' => ['required', 'exists:roles,id'],
            'discount_type' => ['nullable', 'in:flat,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0']
        ];
    }
}
