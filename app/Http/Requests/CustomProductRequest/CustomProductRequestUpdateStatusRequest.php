<?php

namespace App\Http\Requests\CustomProductRequest;

use Illuminate\Foundation\Http\FormRequest;

class CustomProductRequestUpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string'
        ];
    }
}
