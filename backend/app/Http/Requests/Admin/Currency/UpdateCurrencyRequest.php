<?php

namespace App\Http\Requests\Admin\Currency;

use App\Domains\Currency\Rules\RoundingRule;
use App\Domains\Shared\Rules\DecimalString;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCurrencyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rounding_rule' => ['sometimes', 'nullable', new RoundingRule($this->route('currency')->exponent)],
            'markup_pct' => ['sometimes', DecimalString::percent()],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
