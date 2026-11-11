<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Payments\Models\PaymentGateway;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Currencies (spec Phase 9.7): active for display or not, the markup on the provider rate, rounding and order. The base currency and the gateway charge currencies are fixed (OD-1) and shown, not edited.
 */
class CurrencyScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'pricing';
    }

    public static function slug(): string
    {
        return 'currencies';
    }

    public static function viewPermission(): string
    {
        return 'pricing.view|currency.manage';
    }

    public static function managePermission(): string
    {
        return 'currency.manage';
    }

    public static function model(): string
    {
        return Currency::class;
    }

    public static function table(): string
    {
        return 'currencies';
    }

    protected function apiPath(): string
    {
        return '/admin/api/currencies';
    }

    public static function creatable(): bool
    {
        return false;
    }

    public static function deletable(): bool
    {
        return false;
    }

    public function panel(): ?string
    {
        return 'admin.pricing.currency-panel';
    }

    public function panelData(Model $record): array
    {
        return [
            'base' => app(ExchangeRateRegistry::class)->baseCurrency(),
            'rate' => ExchangeRate::query()->where('quote_currency', $record->getKey())->first(),
            'gateways' => PaymentGateway::query()->orderBy('name')->get(['id', 'name', 'default_currency', 'is_active']),
        ];
    }

    public function fields(?Model $record): array
    {
        return [
            'currency' => [
                Field::text('code', $this->l('code'))->ltr()->using(static fn (Model $currency): string => (string) $currency->getKey())->help(__('admin.help.fixed')),
                Field::toggle('is_active', $this->l('is_active_display')),
                Field::decimal('markup_pct', $this->l('markup_pct'))->help(__('admin.help.markup_pct')),
                Field::number('sort_order', $this->l('sort_order')),
                Field::json('rounding_rule', $this->l('rounding_rule'))->nullable()->help(__('admin.help.rounding_json')),
            ],
        ];
    }
}
