<?php

namespace App\Domains\Currency\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Currency\Models\ExchangeRateHistory;
use App\Domains\Shared\ValueObjects\Percentage;
use App\Domains\Shared\ValueObjects\Rate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Lowest-level writer of exchange rates. Computes the effective rate, appends history and
 * clears the rate cache once the surrounding transaction has committed.
 */
class ExchangeRateWriter
{
    public const string CACHE_KEY = 'fx:current';

    public const string MANUAL_SOURCE = 'manual';

    public function __construct(private AuditLogger $audit) {}

    /**
     * Provider rate with markup, or the active manual override, which replaces it.
     */
    public function effectiveRate(ExchangeRate $rate, Currency $currency): Rate
    {
        if ($rate->is_manual_active && $rate->manual_rate !== null) {
            return Rate::of($rate->manual_rate);
        }

        return Rate::of($rate->provider_rate)->withMarkup(Percentage::of($currency->markup_pct));
    }

    public function appendHistory(ExchangeRate $rate, ?int $changedBy = null): ExchangeRateHistory
    {
        $history = (new ExchangeRateHistory)->forceFill([
            'base_currency' => $rate->base_currency,
            'quote_currency' => $rate->quote_currency,
            'provider_rate' => $rate->provider_rate,
            'effective_rate' => $rate->effective_rate,
            'source' => $rate->is_manual_active ? self::MANUAL_SOURCE : $rate->source,
            'is_manual' => (bool) $rate->is_manual_active,
            'changed_by' => $changedBy,
            'captured_at' => now(),
        ]);

        $history->save();

        return $history;
    }

    public function forgetCache(): void
    {
        DB::afterCommit(static fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * End any manual override whose expiry has passed, restoring the provider rate. Safe to
     * run concurrently: the row lock lets one caller do the work and the others find nothing.
     *
     * @return list<string> the quote currencies that changed
     */
    public function expireDueOverrides(?string $quoteCurrency = null): array
    {
        $due = ExchangeRate::query()
            ->where('is_manual_active', true)
            ->whereNotNull('manual_expires_at')
            ->where('manual_expires_at', '<=', now())
            ->when($quoteCurrency !== null, static fn ($query) => $query->where('quote_currency', $quoteCurrency))
            ->pluck('id');

        $expired = [];

        foreach ($due as $id) {
            DB::transaction(function () use ($id, &$expired): void {
                $rate = ExchangeRate::query()->lockForUpdate()->find($id);

                if ($rate === null || ! $rate->is_manual_active || $rate->manual_expires_at === null || $rate->manual_expires_at->isFuture()) {
                    return;
                }

                $this->endManualOverride($rate);

                $this->audit->record(new AuditEntry(action: 'currency.override_expired', subject: $rate, after: ['quote_currency' => $rate->quote_currency]));

                $expired[] = $rate->quote_currency;
            });
        }

        return $expired;
    }

    /**
     * Switch a row back to its provider rate. With no provider rate to return to, the row is
     * removed from use by deleting it; the history rows remain.
     */
    public function endManualOverride(ExchangeRate $rate, ?int $changedBy = null): void
    {
        if ($rate->provider_rate === null) {
            $rate->delete();
            $this->forgetCache();

            return;
        }

        $currency = Currency::query()->findOrFail($rate->quote_currency);

        $rate->forceFill(['is_manual_active' => false, 'manual_rate' => null, 'manual_expires_at' => null]);
        $rate->forceFill(['effective_rate' => $this->effectiveRate($rate, $currency)->value])->save();

        $this->appendHistory($rate, $changedBy);
        $this->forgetCache();
    }
}
