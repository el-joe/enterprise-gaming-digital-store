<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    /**
     * Delivery values are checked field by field against the product by DeliveryFieldValidator.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'string', 'size:26', 'alpha_num:ascii'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'delivery_values' => ['sometimes', 'array', 'max:20'],
            'custom_amount' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['custom_amount.prohibited' => 'Custom amounts cannot be bought yet.'];
    }
}
