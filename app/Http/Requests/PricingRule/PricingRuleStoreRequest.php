<?php

namespace App\Http\Requests\PricingRule;

use Illuminate\Foundation\Http\FormRequest;

class PricingRuleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255|unique:pricing_rules,name',
            'sale_multiplier' => 'required|numeric|min:0',
            'outlet_multiplier' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'required|boolean',
        ];
    }
}
