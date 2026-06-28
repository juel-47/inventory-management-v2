<?php

namespace App\Http\Requests\Discount;

use Illuminate\Foundation\Http\FormRequest;

class DiscountUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:discounts,name,' . $this->route('id'),
            'type' => 'required|in:flat,percent',
            'value' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'required|boolean',
        ];
    }
}
