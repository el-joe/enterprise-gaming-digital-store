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
     *
     * `default_currency` is each gateway's fixed charge currency (owner decision 1, OD-1): Paymob
     * always charges EGP; Stripe, PayPal and the wallet always charge USD, whatever the display
     * currency. The former `settings.charge_display_currency_only` on Paymob is removed.
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
                'settings' => null,
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
