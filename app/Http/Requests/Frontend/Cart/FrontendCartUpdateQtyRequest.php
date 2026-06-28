<?php

namespace App\Http\Requests\Frontend\Cart;

use Illuminate\Foundation\Http\FormRequest;

class FrontendCartUpdateQtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cart_id'    => 'nullable|integer|exists:carts,id',
            'product_id' => 'nullable|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
        ];
    }
}
