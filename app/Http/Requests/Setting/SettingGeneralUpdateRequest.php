<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class SettingGeneralUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'site_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:4096'],
        ];
    }
}
