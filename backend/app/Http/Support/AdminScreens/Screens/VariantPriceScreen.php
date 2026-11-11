<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Pricing\Models\VariantPrice;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Country and currency price overrides of a variant (spec Phase 8 "country-specific pricing"); the margin guard still applies.
 */
class VariantPriceScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'pricing';
    }

    public static function slug(): string
    {
        return 'variant-prices';
    }

    public static function viewPermission(): string
    {
        return 'pricing.view';
    }

    public static function managePermission(): string
    {
        return 'pricing.manage';
    }

    public static function model(): string
    {
        return VariantPrice::class;
    }

    public static function table(): string
    {
        return 'variant-prices';
    }

    protected function apiPath(): string
    {
        return '/admin/api/variant-prices';
    }

    public function title(?Model $record): string
    {
        return $record === null ? parent::title(null) : (string) __('admin.screens.variant-prices.title', ['id' => $record->getKey()]);
    }

    public function fields(?Model $record): array
    {
        return [
            'price' => [
                Field::number('product_variant_id', $this->l('product_variant_id'))->required()->help(__('admin.help.variant_id')),
                Field::select('country_code', $this->l('country_code'), ['*' => __('admin.common.all_countries')] + Options::countries())->required(),
                Field::select('currency', $this->l('currency'), Options::currencies())->required(),
                Field::decimal('price', $this->l('price'))->required(),
                Field::decimal('compare_at', $this->l('compare_at'))->nullable(),
                Field::text('override_reason', $this->l('override_reason'))->nullable()->max(1000)->help(__('admin.help.override_reason')),
                Field::datetime('starts_at', $this->l('starts_at')),
                Field::datetime('ends_at', $this->l('ends_at')),
            ],
        ];
    }
}
