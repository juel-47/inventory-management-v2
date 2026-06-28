<?php

namespace App\Http\Requests\FrontendOrder;

use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,approved,cancelled',
        ];
    }
}
