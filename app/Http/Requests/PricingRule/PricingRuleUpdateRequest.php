<?php

namespace App\Http\Requests\PricingRule;

use Illuminate\Foundation\Http\FormRequest;

class PricingRuleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255|unique:pricing_rules,name,' . $this->route('id'),
            'sale_multiplier' => 'required|numeric|min:0',
            'outlet_multiplier' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'required|boolean',
        ];
    }
}
