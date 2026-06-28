<?php

namespace App\Http\Requests\IssueReturn;

use Illuminate\Foundation\Http\FormRequest;

class IssueReturnGetItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'issue_id' => 'required|exists:issues,id'
        ];
    }
}
