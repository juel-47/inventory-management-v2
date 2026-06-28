<?php

namespace App\Http\Requests\Color;

use Illuminate\Foundation\Http\FormRequest;

class ColorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|unique:colors,name|max:255',
            'hex_code' => 'nullable|string|max:7',
            'status' => 'required|boolean'
        ];
    }
}
