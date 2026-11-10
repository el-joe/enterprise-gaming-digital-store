<?php

namespace App\Domains\Currency\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Currency\Models\ExchangeRateHistory;
use App\Domains\Identity\Models\User;
use App\Domains\Pricing\Services\PricingDirtyMarker;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\Services\ListQueryApplier;
use App\Domains\Shared\ValueObjects\Rate;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Staff actions on rates: manual overrides and decisions on rates held by the spike guard.
 */
class ExchangeRateAdminService
{
    public function __construct(
        private ListQueryApplier $lists,
        private ExchangeRateWriter $writer,
        private ExchangeRateRegistry $registry,
        private AuditLogger $audit,
        private PricingDirtyMarker $dirty,
    ) {}

    public function list(ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(ExchangeRate::query(), $list, ['is_manual_active' => 'bool'], ['id', 'quote_currency', 'fetched_at'], ['quote_currency'], 'quote_currency');
    }

    public function historyFor(Currency $currency, ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            ExchangeRateHistory::query()->where('quote_currency', $currency->code),
            $list,
            sorts: ['id', 'captured_at'],
        );
    }

    public function override(Currency $currency, Rate $rate, ?CarbonInterface $expiresAt, string $reason, User $actor): ExchangeRate
    {
        $this->assertNotBase($currency);
        $base = $this->registry->baseCurrency();

        $row = DB::transaction(function () use ($currency, $rate, $expiresAt, $reason, $actor, $base): ExchangeRate {
            $row = ExchangeRate::query()->where('base_currency', $base)->where('quote_currency', $currency->code)->lockForUpdate()->first()
                ?? new ExchangeRate(['base_currency' => $base, 'quote_currency' => $currency->code]);

            $before = $row->exists ? ['effective_rate' => $row->effective_rate, 'is_manual_active' => $row->is_manual_active] : null;

            $row->forceFill([
                'manual_rate' => $rate->value,
                'is_manual_active' => true,
                'manual_expires_at' => $expiresAt,
                'effective_rate' => $rate->value,
                'source' => $row->provider_rate !== null ? $row->source : ExchangeRateWriter::MANUAL_SOURCE,
                'fetched_at' => $row->exists ? $row->fetched_at : now(),
            ])->save();

            $this->writer->appendHistory($row, $actor->id);
            $this->writer->forgetCache();

            $this->audit->record(new AuditEntry(
                action: 'currency.override_set',
                subject: $row,
                before: $before,
                after: ['manual_rate' => $rate->value, 'expires_at' => $expiresAt?->toIso8601String()],
                reason: $reason,
            ));

            return $row;
        });

        $this->dirty->forCostCurrencies([$currency->code]);

        return $row->refresh();
    }

    public function clearOverride(Currency $currency, string $reason, User $actor): void
    {
        DB::transaction(function () use ($currency, $reason, $actor): void {
            $row = $this->lockedRow($currency);

            if (! $row->is_manual_active) {
                throw new ApplicationException('This currency has no active override.', 'conflict', 409);
            }

            $before = ['manual_rate' => $row->manual_rate];

            $this->writer->endManualOverride($row, $actor->id);

            $this->audit->record(new AuditEntry(action: 'currency.override_cleared', subject: $row, before: $before, reason: $reason));
        });

        $this->dirty->forCostCurrencies([$currency->code]);
    }

    /**
     * Apply a held rate. A second approval finds nothing pending and is refused.
     */
    public function approvePending(Currency $currency, string $reason, User $actor): ExchangeRate
    {
        $row = DB::transaction(function () use ($currency, $reason, $actor): ExchangeRate {
            $row = $this->lockedRow($currency);

            if ($row->pending_rate === null) {
                throw new ApplicationException('There is no held rate to approve.', 'conflict', 409);
            }

            $before = ['provider_rate' => $row->provider_rate];
            $approved = $row->pending_rate;

            $row->forceFill(['provider_rate' => $approved, 'fetched_at' => $row->pending_since, 'pending_rate' => null, 'pending_since' => null]);
            $row->forceFill(['effective_rate' => $this->writer->effectiveRate($row, $currency)->value])->save();

            $this->writer->appendHistory($row, $actor->id);
            $this->writer->forgetCache();

            $this->audit->record(new AuditEntry(action: 'currency.rate_approved', subject: $row, before: $before, after: ['provider_rate' => $approved], reason: $reason));

            return $row;
        });

        $this->dirty->forCostCurrencies([$currency->code]);

        return $row->refresh();
    }

    public function rejectPending(Currency $currency, string $reason): ExchangeRate
    {
        return DB::transaction(function () use ($currency, $reason): ExchangeRate {
            $row = $this->lockedRow($currency);

            if ($row->pending_rate === null) {
                throw new ApplicationException('There is no held rate to reject.', 'conflict', 409);
            }

            $rejected = $row->pending_rate;

            $row->forceFill(['pending_rate' => null, 'pending_since' => null])->save();

            $this->audit->record(new AuditEntry(action: 'currency.rate_rejected', subject: $row, before: ['pending_rate' => $rejected], reason: $reason));

            return $row->refresh();
        });
    }

    private function lockedRow(Currency $currency): ExchangeRate
    {
        $row = ExchangeRate::query()
            ->where('base_currency', $this->registry->baseCurrency())
            ->where('quote_currency', $currency->code)
            ->lockForUpdate()
            ->first();

        if ($row === null) {
            throw new ApplicationException("No exchange rate exists for {$currency->code}.", 'not_found', 404);
        }

        return $row;
    }

    private function assertNotBase(Currency $currency): void
    {
        if ($currency->code === $this->registry->baseCurrency()) {
            throw new ApplicationException('The base currency has no exchange rate.', 'base_currency_rate', 422);
        }
    }
}
