<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Shared\Models\Country;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Country currency (OD-1 display currency): the currency a customer from this country browses in when it is active with a fresh rate.
 */
class CountryScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'pricing';
    }

    public static function slug(): string
    {
        return 'countries';
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
        return Country::class;
    }

    public static function table(): string
    {
        return 'countries';
    }

    protected function apiPath(): string
    {
        return '/admin/api/countries';
    }

    public static function creatable(): bool
    {
        return false;
    }

    public static function deletable(): bool
    {
        return false;
    }

    public function fields(?Model $record): array
    {
        return [
            'country' => [
                Field::text('code', $this->l('code'))->ltr()->using(static fn (Model $country): string => (string) $country->getKey())->help(__('admin.help.fixed')),
                Field::select('default_currency', $this->l('default_currency'), Options::currencies())->nullable()->help(__('admin.help.country_currency')),
                Field::toggle('is_active', $this->l('is_active')),
            ],
        ];
    }
}
