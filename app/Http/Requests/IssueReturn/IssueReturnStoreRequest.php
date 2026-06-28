<?php

namespace App\Http\Requests\IssueReturn;

use Illuminate\Foundation\Http\FormRequest;

class IssueReturnStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'issue_id' => 'required|exists:issues,id',
            'note' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.condition' => 'required|in:good,damaged',
        ];
    }
}
