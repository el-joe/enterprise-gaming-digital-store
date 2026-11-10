<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Payments\Models\PaymentGatewayFeeRule;
use App\Domains\Pricing\DTOs\MarginThreshold;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Support\Collection;

/**
 * Computes the lowest base price that still earns the required margin after supplier cost,
 * gateway fee and conversion cost. Where several gateways could apply, the most expensive one
 * is assumed.
 */
class MarginGuard
{
    private const int WORKING_SCALE = 24;

    public function __construct(
        private SettingsService $settings,
        private ExchangeRateRegistry $rates,
        private CurrencyConverter $converter,
    ) {}

    /**
     * @param  Collection<int, PaymentGatewayFeeRule>  $feeRules
     * @return Money|null null when no price can reach the margin
     */
    public function floor(Money $cost, MarginThreshold $threshold, Collection $feeRules, ?string $country, string $currency, bool $involvesConversion): ?Money
    {
        $conversionPct = $involvesConversion ? (string) $this->settings->get('pricing', 'conversion_cost_pct', '0') : '0';

        $floor = null;

        foreach ($this->fees($feeRules, $country, $currency) as [$percent, $fixed]) {
            $candidate = $this->floorForFee($cost->amount, $threshold, $percent, $fixed, $conversionPct);

            if ($candidate === null) {
                return null;
            }

            $floor = $floor === null || bccomp($candidate, $floor, self::WORKING_SCALE) === 1 ? $candidate : $floor;
        }

        return Money::of($this->ceilToSix((string) $floor), $cost->currency);
    }

    /**
     * @return list<array{0: string, 1: string}> percent and fixed fee in base currency
     */
    private function fees(Collection $feeRules, ?string $country, string $currency): array
    {
        $fees = [];

        foreach ($feeRules as $rule) {
            /** @var PaymentGatewayFeeRule $rule */
            if (($rule->country_code !== null && $rule->country_code !== $country) || ($rule->currency !== null && $rule->currency !== $currency)) {
                continue;
            }

            $fixed = $this->fixedFeeInBase($rule);

            if ($fixed !== null) {
                $fees[] = [$rule->percent_fee, $fixed];
            }
        }

        return $fees !== [] ? $fees : [[
            (string) $this->settings->get('pricing', 'default_gateway_fee_pct', '0'),
            (string) $this->settings->get('pricing', 'default_gateway_fee_fixed', '0'),
        ]];
    }

    private function fixedFeeInBase(PaymentGatewayFeeRule $rule): ?string
    {
        if ($rule->fixed_fee_currency === null || bccomp($rule->fixed_fee, '0', 6) === 0) {
            return $rule->fixed_fee;
        }

        try {
            return $this->converter->toBase(Money::of($rule->fixed_fee, $rule->fixed_fee_currency))->amount;
        } catch (ExchangeRateMissing) {
            return null;
        }
    }

    /**
     * Net = P * (1 - fees%) - cost - fixed. Require net >= P * margin% and net >= fixed margin.
     */
    private function floorForFee(string $cost, MarginThreshold $threshold, string $feePct, string $feeFixed, string $conversionPct): ?string
    {
        $keep = bcsub('1', bcdiv(bcadd($feePct, $conversionPct, self::WORKING_SCALE), '100', self::WORKING_SCALE), self::WORKING_SCALE);
        $keepAfterMargin = bcsub($keep, bcdiv($threshold->percent, '100', self::WORKING_SCALE), self::WORKING_SCALE);

        if (bccomp($keep, '0', self::WORKING_SCALE) !== 1 || bccomp($keepAfterMargin, '0', self::WORKING_SCALE) !== 1) {
            return null;
        }

        $outgoings = bcadd($cost, $feeFixed, self::WORKING_SCALE);

        $forFixedMargin = bcdiv(bcadd($outgoings, $threshold->fixedBase, self::WORKING_SCALE), $keep, self::WORKING_SCALE);
        $forPercentMargin = bcdiv($outgoings, $keepAfterMargin, self::WORKING_SCALE);

        return bccomp($forFixedMargin, $forPercentMargin, self::WORKING_SCALE) === 1 ? $forFixedMargin : $forPercentMargin;
    }

    /**
     * Round up to six places: a floor rounded down would sit below the required margin.
     */
    private function ceilToSix(string $value): string
    {
        $truncated = bcadd($value, '0', Money::SCALE);

        return bccomp($value, $truncated, self::WORKING_SCALE) === 1
            ? bcadd($truncated, '0.000001', Money::SCALE)
            : $truncated;
    }
}
