<?php

namespace App\Http\Requests\Admin\Pricing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimulatePriceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'variant' => ['required', 'string', 'size:26', Rule::exists('product_variants', 'public_id')->whereNull('deleted_at')],
            'country_code' => ['sometimes', 'nullable', 'string', 'size:2', Rule::exists('countries', 'code')],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3', Rule::exists('currencies', 'code')],
        ];
    }
}
