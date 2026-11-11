<?php

namespace App\Http\Requests\Admin\Payments;

use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Shared\Enums\EnvironmentMode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Write-only gateway credentials: every field the adapter reads for the mode
 * (`admin.credential_fields.gateways`), replaced together. Stripe keys must match the mode
 * (`sk_test_`/`pk_test_` in sandbox, `sk_live_`/`pk_live_` in production), so a test key never
 * pairs with live settings (SECURITY.md section 6).
 */
class SetGatewayCredentialsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'mode' => ['required', Rule::enum(EnvironmentMode::class)],
            'credentials' => ['required', 'array'],
            'webhook_secret' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];

        foreach ($this->fields() as $field) {
            $rules["credentials.{$field}"] = ['required', 'string', 'min:4', 'max:500'];
        }

        return $rules;
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->gateway()->adapter_key !== 'stripe' || $validator->errors()->isNotEmpty()) {
                return;
            }

            $live = $this->input('mode') === EnvironmentMode::Production->value;

            foreach (['secret_key' => $live ? 'sk_live_' : 'sk_test_', 'publishable_key' => $live ? 'pk_live_' : 'pk_test_'] as $field => $prefix) {
                if (! str_starts_with((string) $this->input("credentials.{$field}"), $prefix)) {
                    $validator->errors()->add("credentials.{$field}", __('admin.gateways.prefix', ['prefix' => $prefix]));
                }
            }
        }];
    }

    /**
     * Only the declared fields, in declared order.
     *
     * @return array<string, string>
     */
    public function credentials(): array
    {
        $values = [];

        foreach ($this->fields() as $field) {
            $values[$field] = (string) $this->validated("credentials.{$field}");
        }

        return $values;
    }

    /**
     * @return list<string>
     */
    private function fields(): array
    {
        return (array) config('admin.credential_fields.gateways.'.$this->gateway()->adapter_key, []);
    }

    private function gateway(): PaymentGateway
    {
        /** @var PaymentGateway $gateway */
        $gateway = $this->route('gateway');

        return $gateway;
    }
}
