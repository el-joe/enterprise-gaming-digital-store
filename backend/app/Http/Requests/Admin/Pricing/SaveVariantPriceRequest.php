<?php

namespace App\Http\Requests\Admin\Pricing;

use App\Domains\Shared\Rules\DecimalString;
use App\Http\Requests\Admin\AdminSaveRequest;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaveVariantPriceRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_variant_id' => [$this->presence(), 'integer', Rule::exists('product_variants', 'id')->whereNull('deleted_at')],
            'country_code' => [$this->presence(), 'string', $this->countryIsKnown(...)],
            'currency' => [$this->presence(), 'string', 'size:3', Rule::exists('currencies', 'code')],
            'price' => [$this->presence(), DecimalString::positiveMoney()],
            'compare_at' => ['sometimes', 'nullable', DecimalString::money()],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after:starts_at'],
            'override_reason' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    private function countryIsKnown(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== '*' && ! DB::table('countries')->where('code', strtoupper((string) $value))->exists()) {
            $fail("The {$attribute} must be a known country or *.");
        }
    }
}
