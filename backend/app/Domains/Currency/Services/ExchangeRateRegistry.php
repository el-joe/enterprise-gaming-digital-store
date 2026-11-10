<?php

namespace App\Domains\Currency\Services;

use App\Domains\Currency\DTOs\ResolvedRate;
use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Currency\Models\ExchangeRateHistory;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Rate;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

/**
 * Cached read model of currencies and current rates. Freshness is computed on each read from
 * the stored fetch time, so a cached entry can never hide a stale rate.
 */
class ExchangeRateRegistry
{
    private const int CACHE_SECONDS = 3600;

    private const int DEFAULT_MAX_AGE_MINUTES = 1440;

    public function __construct(private SettingsService $settings, private ExchangeRateWriter $writer) {}

    public function baseCurrency(): string
    {
        return (string) $this->settings->get('currency', 'base', 'USD');
    }

    /**
     * @return array{code: string, name: string, symbol: string, exponent: int, rounding_rule: array<string, string>|null, is_active: bool, sort_order: int}|null
     */
    public function currency(string $code): ?array
    {
        return $this->snapshot()['currencies'][strtoupper($code)] ?? null;
    }

    /**
     * @return list<array{code: string, name: string, symbol: string, exponent: int, rounding_rule: array<string, string>|null, is_active: bool, sort_order: int}>
     */
    public function activeCurrencies(): array
    {
        return array_values(array_filter($this->snapshot()['currencies'], static fn (array $currency): bool => $currency['is_active']));
    }

    /**
     * @throws ExchangeRateMissing
     */
    public function resolve(string $quote): ResolvedRate
    {
        $quote = strtoupper($quote);
        $base = $this->baseCurrency();

        if ($quote === $base) {
            return new ResolvedRate($base, $base, Rate::of('1'), Rate::of('1'), 'base', null, false, false, null);
        }

        $row = $this->snapshot()['rates'][$quote] ?? null;

        if ($row !== null && $row['manual_expires_at'] !== null && $row['manual_expires_at'] <= now()->getTimestamp()) {
            $this->writer->expireDueOverrides($quote);
            Cache::forget(ExchangeRateWriter::CACHE_KEY);

            $row = $this->snapshot()['rates'][$quote] ?? null;
        }

        if ($row === null) {
            throw new ExchangeRateMissing($quote);
        }

        $fetchedAt = CarbonImmutable::createFromTimestampUTC($row['fetched_at']);

        return new ResolvedRate(
            base: $row['base'],
            quote: $quote,
            rate: Rate::of($row['effective_rate']),
            rateWithoutMarkup: Rate::of($row['is_manual'] ? $row['effective_rate'] : $row['provider_rate']),
            source: $row['is_manual'] ? ExchangeRateWriter::MANUAL_SOURCE : $row['source'],
            fetchedAt: $fetchedAt,
            isManual: $row['is_manual'],
            isStale: ! $row['is_manual'] && $fetchedAt->isBefore(now()->subMinutes($this->maxAgeMinutes())),
            historyId: $row['history_id'],
        );
    }

    public function forget(): void
    {
        Cache::forget(ExchangeRateWriter::CACHE_KEY);
    }

    private function maxAgeMinutes(): int
    {
        return (int) $this->settings->get('currency', 'max_age_minutes', self::DEFAULT_MAX_AGE_MINUTES);
    }

    /**
     * @return array{currencies: array<string, array<string, mixed>>, rates: array<string, array<string, mixed>>}
     */
    private function snapshot(): array
    {
        return Cache::remember(ExchangeRateWriter::CACHE_KEY, self::CACHE_SECONDS, static function (): array {
            $latestHistory = ExchangeRateHistory::query()
                ->selectRaw('quote_currency, MAX(id) AS latest_id')
                ->groupBy('quote_currency')
                ->pluck('latest_id', 'quote_currency');

            return [
                'currencies' => Currency::query()->orderBy('sort_order')->orderBy('code')->get()
                    ->mapWithKeys(static fn (Currency $currency): array => [$currency->code => [
                        'code' => $currency->code,
                        'name' => $currency->name,
                        'symbol' => $currency->symbol,
                        'exponent' => $currency->exponent,
                        'rounding_rule' => $currency->rounding_rule,
                        'is_active' => $currency->is_active,
                        'sort_order' => $currency->sort_order,
                    ]])->all(),
                'rates' => ExchangeRate::query()->get()
                    ->mapWithKeys(static fn (ExchangeRate $rate): array => [$rate->quote_currency => [
                        'base' => $rate->base_currency,
                        'effective_rate' => $rate->effective_rate,
                        'provider_rate' => $rate->provider_rate,
                        'is_manual' => $rate->is_manual_active,
                        'manual_expires_at' => $rate->is_manual_active ? $rate->manual_expires_at?->getTimestamp() : null,
                        'source' => $rate->source,
                        'fetched_at' => $rate->fetched_at->getTimestamp(),
                        'history_id' => isset($latestHistory[$rate->quote_currency]) ? (int) $latestHistory[$rate->quote_currency] : null,
                    ]])->all(),
            ];
        });
    }
}
