<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class SettingCurrencyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency_name' => ['required', 'string', 'max:20'],
            'currency_icon' => ['required', 'string', 'max:10'],
        ];
    }
}
