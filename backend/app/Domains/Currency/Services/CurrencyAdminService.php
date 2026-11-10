<?php

namespace App\Domains\Currency\Services;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Exceptions\ExchangeRateStale;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends CrudService<Currency>
 */
class CurrencyAdminService extends CrudService
{
    public function __construct(
        ListQueryApplier $lists,
        AuditLogger $audit,
        private ExchangeRateRegistry $registry,
        private ExchangeRateWriter $writer,
        private CurrencyConverter $converter,
    ) {
        parent::__construct($lists, $audit);
    }

    protected function model(): string
    {
        return Currency::class;
    }

    protected function auditPrefix(): string
    {
        return 'currency.currency';
    }

    protected function filters(): array
    {
        return ['is_active' => 'bool'];
    }

    protected function sorts(): array
    {
        return ['code', 'name', 'sort_order'];
    }

    protected function searchColumns(): array
    {
        return ['code', 'name'];
    }

    protected function defaultSort(): string
    {
        return 'code';
    }

    /**
     * A non-base currency can only be switched on once it has a real, current rate.
     *
     * @param  Currency  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        $isBase = $model->code === $this->registry->baseCurrency();

        $activating = array_key_exists('is_active', $attributes) ? filter_var($attributes['is_active'], FILTER_VALIDATE_BOOLEAN) : null;

        if ($activating === false && $isBase) {
            throw new ApplicationException('The base currency cannot be deactivated.', 'base_currency_rate');
        }

        if ($activating === true && ! $model->is_active && ! $isBase) {
            $this->assertHasCurrentRate($model);
        }

        return $attributes;
    }

    /**
     * @param  Currency  $model
     */
    protected function afterSave(Model $model, array $attributes): void
    {
        $this->writer->forgetCache();

        if ($model->wasChanged('markup_pct')) {
            $this->applyMarkup($model);
        }
    }

    private function applyMarkup(Currency $currency): void
    {
        $row = ExchangeRate::query()
            ->where('base_currency', $this->registry->baseCurrency())
            ->where('quote_currency', $currency->code)
            ->lockForUpdate()
            ->first();

        if ($row === null || $row->provider_rate === null || $row->is_manual_active) {
            return;
        }

        $row->forceFill(['effective_rate' => $this->writer->effectiveRate($row, $currency)->value])->save();

        $this->writer->appendHistory($row, auth()->id());
    }

    private function assertHasCurrentRate(Currency $currency): void
    {
        try {
            $this->converter->usableAtCheckout($currency->code);
        } catch (ExchangeRateMissing) {
            throw new ApplicationException("{$currency->code} cannot be activated until it has an exchange rate.", 'rate_missing');
        } catch (ExchangeRateStale) {
            throw new ApplicationException("{$currency->code} cannot be activated while its exchange rate is out of date.", 'rate_stale');
        }
    }
}
