<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Exists;
use Locale;

/**
 * The countries checkout accepts (M-A4): one definition shared by the `country` rule of
 * validate, payment-options and place-order and by `GET /storefront/countries`, so the list a
 * client shows is exactly what checkout accepts.
 */
class CountryDirectory
{
    /**
     * The validation rule for a checkout `country` field.
     */
    public static function rule(): Exists
    {
        return (new Exists('countries', 'code'))->where(static fn (QueryBuilder $query): QueryBuilder => self::onlyActive($query));
    }

    /**
     * @return Collection<int, Country>
     */
    public function active(): Collection
    {
        return $this->activeQuery()->orderBy('name')->get()->toBase();
    }

    /**
     * The country name in the request locale: the stored (English) name for the base locale,
     * otherwise the ICU name of the ISO code, falling back to the stored name.
     */
    public function localizedName(Country $country): string
    {
        $locale = app()->getLocale();

        if ($locale === config('app.fallback_locale') || ! class_exists(Locale::class)) {
            return $country->name;
        }

        $name = Locale::getDisplayRegion('-'.$country->code, $locale);

        return is_string($name) && $name !== '' && $name !== $country->code ? $name : $country->name;
    }

    /**
     * @return Builder<Country>
     */
    private function activeQuery(): Builder
    {
        return self::onlyActive(Country::query());
    }

    /**
     * The single definition of "checkout accepts this country".
     *
     * @template TQuery of Builder|QueryBuilder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    private static function onlyActive(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('is_active', true);
    }
}
