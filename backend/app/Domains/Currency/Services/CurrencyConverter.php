<?php

namespace App\Domains\Currency\Services;

use App\Domains\Currency\DTOs\CheckoutCurrencyDecision;
use App\Domains\Currency\DTOs\ConvertedAmount;
use App\Domains\Currency\DTOs\ResolvedRate;
use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Exceptions\ExchangeRateStale;
use App\Domains\Shared\ValueObjects\Money;

/**
 * Converts base-currency money for display and for checkout. Display tolerates a stale rate and
 * flags it; checkout refuses one. Both round identically, so where the display and payment
 * currency match the amounts are equal.
 */
class CurrencyConverter
{
    public function __construct(private ExchangeRateRegistry $registry, private CurrencyRounder $rounder) {}

    /**
     * @throws ExchangeRateMissing
     */
    public function toDisplay(Money $base, string $currency): ConvertedAmount
    {
        return $this->convert($base, $this->registry->resolve($currency));
    }

    /**
     * @throws ExchangeRateMissing
     * @throws ExchangeRateStale
     */
    public function toCheckout(Money $base, string $currency): ConvertedAmount
    {
        return $this->convert($base, $this->usableAtCheckout($currency));
    }

    /**
     * Convert with an already resolved rate: `$rate->rate`, or `$withoutMarkup` for explicit prices.
     */
    public function convert(Money $base, ResolvedRate $rate, bool $withoutMarkup = false): ConvertedAmount
    {
        $currency = $this->registry->currency($rate->quote);

        return new ConvertedAmount(
            $this->rounder->round(
                $base->convertedTo($rate->quote, ($withoutMarkup ? $rate->rateWithoutMarkup : $rate->rate)->value),
                $currency['exponent'] ?? 2,
                $currency['rounding_rule'] ?? null,
            ),
            $rate,
        );
    }

    /**
     * Express a non-base amount in the base currency.
     *
     * @throws ExchangeRateMissing
     */
    public function toBase(Money $money, ?ResolvedRate $rate = null, bool $withoutMarkup = false): Money
    {
        $base = $this->registry->baseCurrency();

        if ($money->currency === $base) {
            return $money;
        }

        $rate ??= $this->registry->resolve($money->currency);

        return Money::of($money->dividedBy(($withoutMarkup ? $rate->rateWithoutMarkup : $rate->rate)->value)->amount, $base);
    }

    public function roundForCurrency(Money $money): Money
    {
        $currency = $this->registry->currency($money->currency);

        return Money::of($money->amount, $money->currency)->roundToCurrency($currency['exponent'] ?? 2);
    }

    /**
     * @throws ExchangeRateMissing
     * @throws ExchangeRateStale
     */
    public function usableAtCheckout(string $currency): ResolvedRate
    {
        $rate = $this->registry->resolve($currency);

        if ($rate->isStale) {
            throw new ExchangeRateStale($rate->quote);
        }

        return $rate;
    }

    /**
     * Whether the code names an active currency (the base currency is always active).
     */
    public function isActiveCurrency(string $code): bool
    {
        if (strtoupper($code) === $this->registry->baseCurrency()) {
            return true;
        }

        return $this->registry->currency($code)['is_active'] ?? false;
    }

    /**
     * The currency to browse in: the request if it is active with a rate, otherwise base.
     */
    public function resolveDisplayCurrency(?string $requested): string
    {
        $base = $this->registry->baseCurrency();

        if ($requested === null || strtoupper($requested) === $base) {
            return $base;
        }

        $currency = $this->registry->currency($requested);

        if ($currency === null || ! $currency['is_active']) {
            return $base;
        }

        try {
            return $this->registry->resolve($requested)->quote;
        } catch (ExchangeRateMissing) {
            return $base;
        }
    }

    /**
     * The currency checkout may use: the request if active with a fresh rate, otherwise base.
     */
    public function resolveCheckoutCurrency(string $requested): CheckoutCurrencyDecision
    {
        $base = $this->registry->baseCurrency();
        $requested = strtoupper($requested);

        if ($requested === $base) {
            return new CheckoutCurrencyDecision($base, false, null);
        }

        $currency = $this->registry->currency($requested);

        if ($currency === null || ! $currency['is_active']) {
            return new CheckoutCurrencyDecision($base, true, 'currency_inactive');
        }

        try {
            $this->usableAtCheckout($requested);
        } catch (ExchangeRateStale) {
            return new CheckoutCurrencyDecision($base, true, 'rate_stale');
        } catch (ExchangeRateMissing) {
            return new CheckoutCurrencyDecision($base, true, 'rate_missing');
        }

        return new CheckoutCurrencyDecision($requested, false, null);
    }

    /**
     * @return array{amount: string, currency: string, formatted: string}
     */
    public function present(Money $money): array
    {
        $currency = $this->registry->currency($money->currency);
        $exponent = $currency['exponent'] ?? 2;
        $symbol = $currency['symbol'] ?? $money->currency;
        $amount = bcadd($money->amount, '0', $exponent);

        return ['amount' => $amount, 'currency' => $money->currency, 'formatted' => $symbol.$amount];
    }
}
