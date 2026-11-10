<?php

namespace App\Http\Requests\Admin\Fulfillment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportDigitalCodesRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')->whereNull('deleted_at')],
            'codes' => ['required', 'array', 'min:1', 'max:'.(int) config('fulfillment.stock_import_max_codes')],
            'codes.*.code' => ['required', 'string', 'min:4', 'max:500', 'distinct:ignore_case'],
            'codes.*.pin' => ['sometimes', 'nullable', 'string', 'max:100'],
            'codes.*.redemption_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url:https'],
            'codes.*.expires_at' => ['sometimes', 'nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Validation messages never echo a code.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['codes.*.code.distinct' => 'This code appears more than once in the batch.'];
    }
}
