<?php

namespace App\Http\Requests\Storefront;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Shared\Rules\DecimalString;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductListingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'category' => ['sometimes', 'string', 'max:140'],
            'platform' => ['sometimes', 'string', 'max:140'],
            'region' => ['sometimes', 'string', 'max:16'],
            'delivery_type' => ['sometimes', Rule::enum(DeliveryType::class)],
            'min_price' => ['sometimes', DecimalString::money()],
            'max_price' => ['sometimes', DecimalString::money()],
            'sort' => ['sometimes', Rule::in(['popular', 'newest', 'price_asc', 'price_desc', 'name'])],
        ];
    }
}
