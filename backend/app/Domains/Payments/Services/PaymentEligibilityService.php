<?php

namespace App\Domains\Payments\Services;

use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Exceptions\ExchangeRateStale;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Customers\Enums\CustomerRiskLevel;
use App\Domains\Payments\Contracts\InternalLedgerGateway;
use App\Domains\Payments\Contracts\PaymentGateway as GatewayAdapter;
use App\Domains\Payments\DTOs\EligibilityContext;
use App\Domains\Payments\DTOs\GatewayEligibility;
use App\Domains\Payments\Enums\PaymentEligibilityRuleEffect;
use App\Domains\Payments\Models\PaymentEligibilityRule;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Payments\Models\PaymentGatewayCurrency;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * PROJECT_ARCHITECTURE.md 12. A gateway is offered only when every check passes: active, an
 * adapter exists, credentials exist for its mode, a payment currency both the provider and the
 * merchant account support (with a fresh rate), the amount is within that currency's limits, no
 * cart product excludes it, and the first matching eligibility rule (by priority) allows it.
 * No matching rule means deny. Country alone never grants eligibility.
 *
 * Rule conditions: `countries` (declared country), `ip_countries` (an unknown IP country never
 * matches), `require_ip_match` (IP country equals declared country), `currencies` (the payment
 * currency), base amount bounds, `allow_guest` (false: the rule applies to signed-in customers
 * only) and `max_risk_level`.
 *
 * The payment currency is fixed per gateway (owner decision 1, OD-1): the gateway's
 * `default_currency` (seeded: Paymob EGP; Stripe, PayPal and the wallet USD), whatever the display
 * currency. It must still be enabled for the merchant and supported by the adapter; the base
 * total is converted to it once with the checkout rate.
 *
 * An internal-ledger gateway (the wallet) needs no credentials, but is offered only to a signed-in,
 * verified, active customer whose wallet balance covers the payment amount (M11).
 */
class PaymentEligibilityService
{
    private const array RISK_ORDER = ['low' => 0, 'medium' => 1, 'high' => 2, 'blocked' => 3];

    public function __construct(
        private PaymentGatewayRegistry $registry,
        private CurrencyConverter $converter,
        private ExchangeRateRegistry $rates,
    ) {}

    /**
     * @return list<GatewayEligibility>
     */
    public function evaluateAll(EligibilityContext $context): array
    {
        return $this->gateways($context)->map(fn (PaymentGateway $gateway): GatewayEligibility => $this->evaluate($gateway, $context))->values()->all();
    }

    public function forKey(string $key, EligibilityContext $context): ?GatewayEligibility
    {
        $gateway = $this->gateways($context, $key)->first();

        return $gateway === null ? null : $this->evaluate($gateway, $context);
    }

    private function evaluate(PaymentGateway $gateway, EligibilityContext $context): GatewayEligibility
    {
        $refuse = static fn (string $reason): GatewayEligibility => new GatewayEligibility($gateway, null, $reason);

        if (! $this->registry->has($gateway->adapter_key)) {
            return $refuse('gateway_unavailable');
        }

        $adapter = $this->registry->for($gateway);

        if (! $adapter instanceof InternalLedgerGateway && ! $this->hasCredentials($gateway)) {
            return $refuse('gateway_unavailable');
        }

        if ($context->riskLevel === CustomerRiskLevel::Blocked) {
            return $refuse('not_eligible');
        }

        if ((int) $gateway->getAttribute('excluded_products_count') > 0) {
            return $refuse('product_excluded');
        }

        $currency = $this->paymentCurrency($gateway, $adapter);

        if ($currency === null) {
            return $refuse('currency_not_supported');
        }

        $amount = $this->paymentAmount($context->totalBase, $currency);

        if ($amount === null) {
            return $refuse('rate_unavailable');
        }

        /** @var PaymentGatewayCurrency $row */
        $row = $gateway->paymentGatewayCurrencies->firstWhere('currency', $currency);

        if (($row->min_amount !== null && bccomp($amount->amount, $row->min_amount, Money::SCALE) < 0)
            || ($row->max_amount !== null && bccomp($amount->amount, $row->max_amount, Money::SCALE) > 0)) {
            return $refuse('amount_out_of_range');
        }

        if (! $this->rulesAllow($gateway->paymentEligibilityRules, $context, $currency)) {
            return $refuse('not_eligible');
        }

        if ($adapter instanceof InternalLedgerGateway && ! $adapter->canCover($context->userId, $amount)) {
            return $refuse('insufficient_balance');
        }

        return new GatewayEligibility($gateway, $currency);
    }

    /**
     * The gateway's fixed charge currency (OD-1), when both the provider and the merchant account
     * can charge it; otherwise none. The display currency plays no part.
     */
    private function paymentCurrency(PaymentGateway $gateway, GatewayAdapter $adapter): ?string
    {
        $currency = $gateway->chargeCurrency();
        $enabled = $gateway->paymentGatewayCurrencies->contains(static fn (PaymentGatewayCurrency $row): bool => $row->is_enabled && strtoupper($row->currency) === $currency);

        return $enabled && $adapter->supportsCurrency($currency) ? $currency : null;
    }

    private function paymentAmount(Money $totalBase, string $currency): ?Money
    {
        if ($currency === $this->rates->baseCurrency()) {
            return $this->converter->roundForCurrency($totalBase);
        }

        try {
            return $this->converter->toCheckout($totalBase, $currency)->money;
        } catch (ExchangeRateMissing|ExchangeRateStale) {
            return null;
        }
    }

    /**
     * @param  Collection<int, PaymentEligibilityRule>  $rules
     */
    private function rulesAllow(Collection $rules, EligibilityContext $context, string $paymentCurrency): bool
    {
        foreach ($rules as $rule) {
            if ($this->matches($rule, $context, $paymentCurrency)) {
                return $rule->effect === PaymentEligibilityRuleEffect::Allow;
            }
        }

        return false;
    }

    private function matches(PaymentEligibilityRule $rule, EligibilityContext $context, string $paymentCurrency): bool
    {
        $upper = static fn (?array $values): array => array_map('strtoupper', $values ?? []);
        $ip = $context->ipCountry === null ? null : strtoupper($context->ipCountry);

        return match (true) {
            $rule->countries !== null && $rule->countries !== [] && ! in_array($context->countryCode, $upper($rule->countries), true) => false,
            $rule->ip_countries !== null && $rule->ip_countries !== [] && ($ip === null || ! in_array($ip, $upper($rule->ip_countries), true)) => false,
            $rule->require_ip_match && $ip !== $context->countryCode => false,
            $rule->currencies !== null && $rule->currencies !== [] && ! in_array($paymentCurrency, $upper($rule->currencies), true) => false,
            $rule->min_amount_base !== null && bccomp($context->totalBase->amount, $rule->min_amount_base, Money::SCALE) < 0 => false,
            $rule->max_amount_base !== null && bccomp($context->totalBase->amount, $rule->max_amount_base, Money::SCALE) > 0 => false,
            ! $rule->allow_guest && $context->isGuest => false,
            $rule->max_risk_level !== null && self::RISK_ORDER[$context->riskLevel->value] > self::RISK_ORDER[$rule->max_risk_level->value] => false,
            default => true,
        };
    }

    private function hasCredentials(PaymentGateway $gateway): bool
    {
        return $gateway->paymentGatewayCredentials->contains(
            static fn (PaymentGatewayCredential $row): bool => $row->mode === $gateway->mode && (string) $row->getRawOriginal('credentials') !== '',
        );
    }

    /**
     * @return Collection<int, PaymentGateway>
     */
    private function gateways(EligibilityContext $context, ?string $key = null): Collection
    {
        return PaymentGateway::query()
            ->where('is_active', true)
            ->when($key !== null, static fn (Builder $query) => $query->where('key', $key))
            ->with([
                'paymentGatewayCurrencies',
                'paymentGatewayCredentials',
                'paymentEligibilityRules' => static fn ($query) => $query->where('is_active', true)->orderBy('priority')->orderBy('id'),
            ])
            ->withCount(['excludedProducts' => static fn (Builder $query) => $query->whereIn('products.id', $context->productIds === [] ? [0] : $context->productIds)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
