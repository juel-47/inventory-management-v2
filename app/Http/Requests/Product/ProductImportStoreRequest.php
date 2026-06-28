<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductImportStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];
        if ($this->has('temp_path')) {
            $rules['temp_path'] = 'required';
            $rules['original_name'] = 'required';
        } else {
            $rules['import_file'] = 'required|mimes:csv,xlsx,xls|max:204800';
        }
        return $rules;
    }

    public function messages(): array
    {
        return [
            'import_file.required' => 'Please upload a file',
            'import_file.mimes' => 'Only CSV, xlsx, and xls files are allowed',
            'import_file.max' => 'File size must be less than 200MB',
        ];
    }
}
