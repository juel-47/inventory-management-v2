<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseUploadAttachmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_attachments' => 'required|array|min:1',
            'invoice_attachments.*' => 'file|mimes:jpeg,png,jpg,pdf,xlsx,xls|max:5120',
        ];
    }
}
