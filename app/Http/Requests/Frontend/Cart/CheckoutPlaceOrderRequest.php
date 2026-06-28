<?php

namespace App\Http\Requests\Frontend\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutPlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'nullable|string|max:255|required_unless:ship_different,1',
            'email' => 'nullable|email|max:255|required_unless:ship_different,1',
            'phone' => 'nullable|string|max:50|required_unless:ship_different,1',
            'address' => 'nullable|string|max:500|required_unless:ship_different,1',
            'outlet_name' => 'nullable|string|max:255',
            'pi_email' => 'nullable|email|max:255',
            'saved_form_id' => 'nullable|integer',
            'ship_different' => 'nullable|boolean',
            'shipping_first_name' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_last_name' => 'nullable|string|max:255',
            'shipping_email' => 'nullable|email|max:255|required_if:ship_different,1',
            'shipping_phone' => 'nullable|string|max:50|required_if:ship_different,1',
            'shipping_street_address' => 'nullable|string|max:500|required_if:ship_different,1',
            'shipping_city' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_state' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_zip_code' => 'nullable|string|max:50|required_if:ship_different,1',
            'shipping_country' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_outlet_name' => 'nullable|string|max:255',
        ];
    }
}
