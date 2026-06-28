<?php

namespace App\Http\Requests\Size;

use Illuminate\Foundation\Http\FormRequest;

class SizeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255|unique:sizes,name,' . $this->route('id'),
            'status' => 'required|boolean'
        ];
    }
}
