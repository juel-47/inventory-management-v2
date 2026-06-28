<?php

namespace App\Http\Requests\Issue;

use Illuminate\Foundation\Http\FormRequest;

class IssueStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'outlet_id' => 'required|exists:users,id',
            'product_request_id' => 'nullable|exists:product_requests,id',
            'order_id' => 'nullable|exists:orders,id',
            'note' => 'nullable|string',
        ];
    }
}
