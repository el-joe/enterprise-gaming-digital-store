<?php

namespace Database\Seeders;

use App\Domains\Payments\Enums\PaymentEligibilityRuleEffect;
use App\Domains\Payments\Models\PaymentEligibilityRule;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Shared\Enums\EnvironmentMode;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Seed inactive gateways with no credentials, plus the default eligibility rules
     * (PROJECT_ARCHITECTURE.md section 12). Merchant currencies stay admin-maintained.
     */
    public function run(): void
    {
        $gateways = [
            'stripe' => ['Stripe', 'USD', 1, [
                [10, PaymentEligibilityRuleEffect::Deny, ['countries' => ['EG']]],
                [100, PaymentEligibilityRuleEffect::Allow, []],
            ]],
            'paypal' => ['PayPal', 'USD', 2, [
                [10, PaymentEligibilityRuleEffect::Deny, ['countries' => ['EG']]],
                [20, PaymentEligibilityRuleEffect::Deny, ['currencies' => ['EGP', 'SAR', 'AED']]],
                [100, PaymentEligibilityRuleEffect::Allow, []],
            ]],
            'paymob' => ['Paymob', 'EGP', 3, [
                [10, PaymentEligibilityRuleEffect::Allow, ['countries' => ['EG'], 'ip_countries' => ['EG'], 'require_ip_match' => true, 'currencies' => ['EGP']]],
            ]],
            'wallet' => ['Wallet', 'USD', 4, [
                [100, PaymentEligibilityRuleEffect::Allow, ['allow_guest' => false]],
            ]],
        ];

        foreach ($gateways as $key => [$name, $defaultCurrency, $sortOrder, $rules]) {
            $gateway = PaymentGateway::query()->updateOrCreate(['key' => $key], [
                'name' => $name,
                'adapter_key' => $key,
                'is_active' => false,
                'mode' => EnvironmentMode::Sandbox,
                'default_currency' => $defaultCurrency,
                'sort_order' => $sortOrder,
                // Paymob is only for transactions configured in EGP: never reached by converting another display currency.
                'settings' => $key === 'paymob' ? ['charge_display_currency_only' => true] : null,
            ]);

            foreach ($rules as [$priority, $effect, $conditions]) {
                PaymentEligibilityRule::query()->updateOrCreate(
                    ['payment_gateway_id' => $gateway->id, 'priority' => $priority],
                    ['effect' => $effect, ...$conditions],
                );
            }
        }
    }
}
