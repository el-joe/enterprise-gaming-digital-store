<?php

namespace App\Http\Requests\Admin\Payments;

use App\Domains\Customers\Enums\CustomerRiskLevel;
use App\Domains\Payments\Enums\PaymentEligibilityRuleEffect;
use App\Domains\Shared\Rules\DecimalString;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A payment eligibility rule (M15): who may pay with the gateway, by declared and IP country,
 * currency, amount in the base currency, guest and risk level.
 */
class SaveEligibilityRuleRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'effect' => ['required', Rule::enum(PaymentEligibilityRuleEffect::class)],
            'countries' => ['present', 'nullable', 'array', 'max:250'],
            'countries.*' => ['string', 'size:2', 'distinct', Rule::exists('countries', 'code')],
            'ip_countries' => ['present', 'nullable', 'array', 'max:250'],
            'ip_countries.*' => ['string', 'size:2', 'distinct', Rule::exists('countries', 'code')],
            'require_ip_match' => ['required', 'boolean'],
            'currencies' => ['present', 'nullable', 'array', 'max:50'],
            'currencies.*' => ['string', 'size:3', 'distinct', Rule::exists('currencies', 'code')],
            'min_amount_base' => ['nullable', DecimalString::money()],
            'max_amount_base' => ['nullable', DecimalString::money()],
            'allow_guest' => ['required', 'boolean'],
            'max_risk_level' => ['nullable', Rule::enum(CustomerRiskLevel::class)],
            'priority' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['countries', 'ip_countries', 'currencies'] as $list) {
            if (is_array($this->input($list))) {
                $this->merge([$list => array_map(static fn (mixed $value): mixed => is_string($value) ? strtoupper(trim($value)) : $value, $this->input($list))]);
            }
        }
    }
}
