<?php

namespace App\Http\Requests\Profile\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,' . Auth::user()->id],
            'image' => ['nullable', 'mimetypes:image/jpeg,image/png,image/gif,image/webp', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:20'],
            'outlet_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ];
    }
}
