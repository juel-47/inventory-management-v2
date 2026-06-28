<?php

namespace App\Http\Requests\Size;

use Illuminate\Foundation\Http\FormRequest;

class SizeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|unique:sizes,name|max:255',
            'status' => 'required|boolean'
        ];
    }
}
