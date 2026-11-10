<?php

namespace App\Domains\Currency\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Currency\Contracts\ExchangeRateProvider;
use App\Domains\Currency\DTOs\ProviderRates;
use App\Domains\Currency\DTOs\RateRefreshResult;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Pricing\Services\PricingDirtyMarker;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Rate;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Applies a provider fetch. The HTTP call happens before any transaction; each currency is
 * then applied in its own short transaction under a row lock.
 */
class ExchangeRateUpdater
{
    private const string UPDATED = 'updated';

    private const string HELD = 'held';

    private const string DUPLICATE = 'duplicate';

    private const string DEFAULT_MAX_CHANGE_PCT = '10';

    public function __construct(
        private ExchangeRateProvider $provider,
        private ExchangeRateWriter $writer,
        private ExchangeRateRegistry $registry,
        private SettingsService $settings,
        private AuditLogger $audit,
        private PricingDirtyMarker $dirty,
    ) {}

    public function refresh(): RateRefreshResult
    {
        $this->writer->expireDueOverrides();

        $base = $this->registry->baseCurrency();
        $currencies = Currency::query()->where('code', '!=', $base)->get()->keyBy('code');

        $fetched = $this->provider->latest($base, $currencies->keys()->all());

        $outcomes = [self::UPDATED => [], self::HELD => [], self::DUPLICATE => []];
        $missing = [];
        $costRatesChanged = [];

        foreach ($currencies as $code => $currency) {
            $rate = $fetched->rates[$code] ?? null;

            if ($rate === null) {
                $missing[] = $code;

                continue;
            }

            [$outcome, $changed] = $this->applyOne($fetched, $currency, $rate);

            $outcomes[$outcome][] = $code;

            if ($changed) {
                $costRatesChanged[] = $code;
            }
        }

        $this->registry->forget();
        $this->dirty->forCostCurrencies($costRatesChanged);

        return new RateRefreshResult($outcomes[self::UPDATED], $outcomes[self::HELD], $outcomes[self::DUPLICATE], $missing);
    }

    /**
     * @return array{0: string, 1: bool} the outcome, and whether the cost conversion rate moved
     */
    private function applyOne(ProviderRates $fetched, Currency $currency, Rate $rate): array
    {
        try {
            return DB::transaction(function () use ($fetched, $currency, $rate): array {
                $row = ExchangeRate::query()
                    ->where('base_currency', $fetched->base)
                    ->where('quote_currency', $currency->code)
                    ->lockForUpdate()
                    ->first();

                if ($row === null) {
                    $row = new ExchangeRate(['base_currency' => $fetched->base, 'quote_currency' => $currency->code]);
                } elseif ($row->provider_rate !== null && $row->fetched_at->getTimestamp() >= $fetched->capturedAt->getTimestamp()) {
                    return [self::DUPLICATE, false];
                }

                if ($this->isSpike($row, $rate)) {
                    $this->hold($row, $fetched, $rate);

                    return [self::HELD, false];
                }

                $previousProviderRate = $row->provider_rate;

                $row->forceFill([
                    'provider_rate' => $rate->value,
                    'source' => $fetched->source,
                    'fetched_at' => $fetched->capturedAt,
                    'pending_rate' => null,
                    'pending_since' => null,
                ]);
                $row->forceFill(['effective_rate' => $this->writer->effectiveRate($row, $currency)->value])->save();

                $this->writer->appendHistory($row);

                return [self::UPDATED, ! $row->is_manual_active && ($previousProviderRate === null || ! Rate::of($previousProviderRate)->equals($rate))];
            });
        } catch (UniqueConstraintViolationException) {
            return [self::DUPLICATE, false];
        }
    }

    private function isSpike(ExchangeRate $row, Rate $rate): bool
    {
        if ($row->provider_rate === null) {
            return false;
        }

        $maxChange = (string) $this->settings->get('currency', 'max_change_pct', self::DEFAULT_MAX_CHANGE_PCT);

        return bccomp($rate->changePercentFrom(Rate::of($row->provider_rate)), $maxChange, Rate::SCALE) === 1;
    }

    /**
     * Keep the old rate in force and record the held one for approval. fetched_at is left alone,
     * so a rate that stays held eventually goes stale and blocks checkout rather than drifting.
     */
    private function hold(ExchangeRate $row, ProviderRates $fetched, Rate $rate): void
    {
        $row->forceFill(['pending_rate' => $rate->value, 'pending_since' => $fetched->capturedAt])->save();

        $this->audit->record(new AuditEntry(
            action: 'currency.rate_held',
            subject: $row,
            before: ['provider_rate' => $row->provider_rate],
            after: ['pending_rate' => $rate->value, 'source' => $fetched->source],
        ));
    }
}
