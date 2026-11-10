<?php

namespace App\Http\Requests\Storefront;

use App\Domains\Shared\Rules\DecimalString;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['idempotency_key' => $this->header('Idempotency-Key')]);
    }

    /**
     * A guest must give an email; a signed-in customer's account email is used instead.
     * `currency` and `expected_total` are what the customer was shown and must still be true.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'string', 'regex:/^[A-Za-z0-9_\-:.]{8,128}$/'],
            'email' => [Rule::requiredIf($this->user() === null), 'nullable', 'email:rfc', 'max:190'],
            'country' => ['required', 'string', 'size:2', Rule::exists('countries', 'code')->where('is_active', true)],
            'gateway' => ['required', 'string', 'max:32', 'alpha_dash:ascii'],
            'method' => ['sometimes', 'nullable', 'string', 'max:64', 'alpha_dash:ascii'],
            'currency' => ['required', 'string', 'size:3', 'alpha:ascii'],
            'expected_total' => ['required', DecimalString::money()],
            'consents' => ['required', 'array'],
            'consents.terms' => ['accepted'],
            'consents.marketing' => ['sometimes', 'boolean'],
            'ga_client_id' => ['sometimes', 'nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9.\-]+$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['idempotency_key.required' => 'The Idempotency-Key header is required.'];
    }

    /**
     * Fingerprint of the validated body, independent of key order, for idempotency checks.
     */
    public function fingerprint(): string
    {
        $body = $this->safe()->except('idempotency_key');
        $body['currency'] = strtoupper((string) $body['currency']);

        return hash('sha256', json_encode(self::sortRecursive($body), JSON_THROW_ON_ERROR));
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function sortRecursive(array $values): array
    {
        ksort($values);

        return array_map(static fn (mixed $value): mixed => is_array($value) ? self::sortRecursive($value) : $value, $values);
    }
}
