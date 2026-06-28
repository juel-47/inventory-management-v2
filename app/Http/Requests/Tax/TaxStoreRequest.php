<?php

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;

class TaxStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:taxes,name',
            'type' => 'required|in:flat,percent',
            'value' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'required|boolean',
        ];
    }
}
