<?php

namespace App\Http\Requests\Frontend\Cart;

use Illuminate\Foundation\Http\FormRequest;

class FrontendCartAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'nullable|integer|min:1',
        ];
    }
}
