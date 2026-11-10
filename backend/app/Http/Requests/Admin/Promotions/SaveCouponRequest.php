<?php

namespace App\Http\Requests\Admin\Promotions;

use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Shared\Rules\DecimalString;
use App\Http\Requests\Admin\AdminSaveRequest;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveCouponRequest extends AdminSaveRequest
{
    /**
     * Money and percentages are decimal strings. `applicable_to` restricts the coupon to products,
     * categories or platforms (any match); empty means the whole cart. `used_count` is never
     * accepted.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $coupon = $this->route('coupon');

        return [
            'code' => [$this->presence(), 'string', 'min:3', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('coupons', 'code')->ignore($coupon)],
            'type' => [$this->presence(), Rule::enum(CouponType::class)],
            'value' => [$this->presence(), DecimalString::positiveMoney(), $this->percentAtMostHundred(...)],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3', Rule::exists('currencies', 'code'), Rule::prohibitedIf($this->input('type') === CouponType::Percent->value)],
            'max_discount_base' => ['sometimes', 'nullable', DecimalString::positiveMoney()],
            'min_order_base' => ['sometimes', 'nullable', DecimalString::money()],
            'usage_limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'applicable_to' => ['sometimes', 'nullable', 'array:products,categories,platforms'],
            'applicable_to.products' => ['sometimes', 'array', 'max:500'],
            'applicable_to.products.*' => ['integer', 'distinct', Rule::exists('products', 'id')],
            'applicable_to.categories' => ['sometimes', 'array', 'max:500'],
            'applicable_to.categories.*' => ['integer', 'distinct', Rule::exists('categories', 'id')],
            'applicable_to.platforms' => ['sometimes', 'array', 'max:500'],
            'applicable_to.platforms.*' => ['integer', 'distinct', Rule::exists('gaming_platforms', 'id')],
            'stackable' => ['sometimes', 'boolean'],
            'allow_below_margin' => ['sometimes', 'boolean'],
            'first_order_only' => ['sometimes', 'boolean'],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function percentAtMostHundred(string $attribute, mixed $value, Closure $fail): void
    {
        $type = $this->input('type') ?? $this->route('coupon')?->type?->value;

        if ($type === CouponType::Percent->value && is_string($value) && bccomp($value, '100', 6) === 1) {
            $fail('A percentage coupon cannot exceed 100.');
        }
    }
}
