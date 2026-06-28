<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class AccountStorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'note' => 'nullable|string',
            'receipts' => 'nullable|array',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ];
    }
}
