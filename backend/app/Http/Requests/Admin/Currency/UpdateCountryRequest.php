<?php

namespace App\Http\Requests\Admin\Currency;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCountryRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'default_currency' => ['sometimes', 'nullable', 'string', 'size:3', Rule::exists('currencies', 'code')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
