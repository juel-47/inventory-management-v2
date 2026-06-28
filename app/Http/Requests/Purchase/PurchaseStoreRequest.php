<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => 'required',
            'date' => 'required|date',
            'pricing_rule_id' => 'nullable|exists:pricing_rules,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'invoice_attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,xlsx,xls|max:51200',
        ];
    }
}
