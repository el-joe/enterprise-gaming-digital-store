<?php

namespace App\Http\Requests\Mcp;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductPricingRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country' => ['sometimes', 'nullable', 'string', 'size:2', 'alpha', Rule::exists('countries', 'code')],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3', 'alpha', Rule::exists('currencies', 'code')],
        ];
    }
}
