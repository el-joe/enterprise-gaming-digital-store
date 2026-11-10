<?php

namespace App\Http\Requests\Account;

use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Identity\DTOs\ProfileChanges;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PATCH /account/profile (M-A5). Every field is optional; `locale`, `display_currency`,
 * `country_code` and `phone` accept null to clear. `locale` is a supported locale (`en`, `ar`),
 * `display_currency` an active currency, `country_code` an active country, `phone` E.164.
 */
class UpdateProfileRequest extends FormRequest
{
    private const array FIELDS = ['name', 'locale', 'display_currency', 'country_code', 'phone'];

    protected function prepareForValidation(): void
    {
        foreach (['display_currency', 'country_code'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => strtoupper(trim($this->input($field)))]);
            }
        }

        if (is_string($this->input('phone'))) {
            $this->merge(['phone' => preg_replace('/[\s\-().]/', '', $this->input('phone'))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'locale' => ['sometimes', 'nullable', 'string', Rule::in(config('app.supported_locales'))],
            'display_currency' => ['sometimes', 'nullable', 'string', 'size:3', static function (string $attribute, mixed $value, Closure $fail): void {
                if (is_string($value) && ! app(CurrencyConverter::class)->isActiveCurrency($value)) {
                    $fail(__('The :attribute must be the code of an active currency.'));
                }
            }],
            'country_code' => ['sometimes', 'nullable', 'string', 'size:2', Rule::exists('countries', 'code')->where('is_active', true)],
            'phone' => ['sometimes', 'nullable', 'string', 'regex:/^\+[1-9]\d{6,14}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['phone.regex' => __('The phone number must be in international format, for example +201001234567.')];
    }

    public function toChanges(): ProfileChanges
    {
        return new ProfileChanges(array_intersect_key($this->validated(), array_flip(self::FIELDS)));
    }
}
