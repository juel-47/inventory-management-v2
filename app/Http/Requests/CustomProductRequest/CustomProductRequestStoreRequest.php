<?php

namespace App\Http\Requests\CustomProductRequest;

use Illuminate\Foundation\Http\FormRequest;

class CustomProductRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_description' => 'required|string|min:10',
            'product_name' => 'nullable|string|max:255',
            'example_image' => 'nullable|array',
            'example_image.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'quantity_needed' => 'required|integer|min:1',
            'expected_price' => 'nullable|numeric|min:0',
        ];
    }
}
