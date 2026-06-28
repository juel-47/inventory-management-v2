<?php

namespace App\Http\Requests\Color;

use Illuminate\Foundation\Http\FormRequest;

class ColorUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255|unique:colors,name,' . $this->route('id'),
            'hex_code' => 'nullable|string|max:7',
            'status' => 'required|boolean'
        ];
    }
}
