<?php

namespace App\Domains\Currency\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Shared\Models\Country;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * A country's display currency (OD-1, ADR-041): `DisplayCurrencyResolver` shows a customer from
 * this country prices in `default_currency` when that currency is active with a fresh rate.
 * Payment currencies stay fixed per gateway. Audited.
 */
class CountryCurrencyService
{
    public function __construct(private AuditLogger $audit) {}

    /**
     * @param  array{default_currency?: string|null, is_active?: bool}  $changes
     */
    public function update(Country $country, array $changes): Country
    {
        $data = Arr::only($changes, ['default_currency', 'is_active']);

        DB::transaction(function () use ($country, $data): void {
            $before = Arr::only($country->getOriginal(), array_keys($data));
            $country->fill($data)->save();

            $this->audit->record(new AuditEntry(
                action: 'currency.country_currency.updated',
                subject: $country,
                before: $before,
                after: Arr::only($country->attributesToArray(), array_keys($data)),
            ));
        });

        return $country->refresh();
    }
}
