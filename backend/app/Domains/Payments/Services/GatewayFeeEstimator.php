<?php

namespace App\Domains\Payments\Services;

use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGatewayFeeRule;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Money;

/**
 * Estimated gateway fee for a payment, in the payment currency, from `payment_gateway_fee_rules`
 * (architecture 11.5): rules for the gateway whose method, country and currency are empty or
 * match; where several match, the most expensive. No rule: the `pricing.default_gateway_fee_*`
 * settings. A fixed fee without a currency is in the base currency, as in the margin guard.
 * Used only where the provider does not report its fee.
 */
class GatewayFeeEstimator
{
    public function __construct(
        private SettingsService $settings,
        private CurrencyConverter $converter,
        private ExchangeRateRegistry $rates,
    ) {}

    public function estimate(Payment $payment, string $countryCode): Money
    {
        $amount = Money::of($payment->amount, $payment->currency);
        $rules = PaymentGatewayFeeRule::query()
            ->where('payment_gateway_id', $payment->payment_gateway_id)
            ->where('is_active', true)
            ->get()
            ->filter(static fn (PaymentGatewayFeeRule $rule): bool => ($rule->payment_method_code === null || $rule->payment_method_code === $payment->method_code)
                && ($rule->country_code === null || $rule->country_code === $countryCode)
                && ($rule->currency === null || $rule->currency === $payment->currency));

        $candidates = $rules->map(fn (PaymentGatewayFeeRule $rule): ?Money => $this->fee($amount, $payment, $rule->percent_fee, $rule->fixed_fee, $rule->fixed_fee_currency))->filter();

        if ($candidates->isEmpty()) {
            $candidates = collect([$this->fee(
                $amount,
                $payment,
                (string) $this->settings->get('pricing', 'default_gateway_fee_pct', '0'),
                (string) $this->settings->get('pricing', 'default_gateway_fee_fixed', '0'),
                null,
            )])->filter();
        }

        return $candidates->reduce(static fn (?Money $max, Money $fee): Money => $max === null || $fee->compareTo($max) > 0 ? $fee : $max) ?? Money::zero($payment->currency);
    }

    private function fee(Money $amount, Payment $payment, string $percent, string $fixed, ?string $fixedCurrency): ?Money
    {
        $variable = $amount->multipliedBy(bcdiv($percent, '100', 12));

        if (bccomp($fixed, '0', 6) === 0) {
            return $variable;
        }

        $fixedCurrency ??= $this->rates->baseCurrency();

        if ($fixedCurrency === $payment->currency) {
            return $variable->plus(Money::of($fixed, $payment->currency));
        }

        try {
            $fixedBase = $this->converter->toBase(Money::of($fixed, $fixedCurrency), withoutMarkup: true);
        } catch (ExchangeRateMissing) {
            return null;
        }

        return $variable->plus($fixedBase->convertedTo($payment->currency, $payment->rate));
    }
}
