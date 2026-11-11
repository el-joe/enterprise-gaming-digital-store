<?php

namespace App\Http\Support\AdminScreens;

use App\DataTables\Cell;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Region;
use App\Domains\Cms\Models\BlogCategory;
use App\Domains\Cms\Models\FaqGroup;
use App\Domains\Currency\Models\Currency;
use App\Domains\Shared\Models\Country;
use App\Domains\Suppliers\Models\Supplier;

/**
 * Select options for admin forms (M15), one query per list per request.
 */
final class Options
{
    /**
     * @param  list<\BackedEnum>  $cases
     * @return array<string, string>
     */
    public static function enum(string $group, array $cases): array
    {
        $options = [];

        foreach ($cases as $case) {
            $options[(string) $case->value] = Cell::label($group, (string) $case->value);
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function platforms(): array
    {
        return once(static fn (): array => GamingPlatform::query()->orderBy('sort_order')->orderBy('name')->pluck('name', 'id')->all());
    }

    /**
     * @return array<int, string>
     */
    public static function categories(): array
    {
        return once(static fn (): array => Category::query()->orderBy('sort_order')->orderBy('name')->pluck('name', 'id')->all());
    }

    /**
     * @return array<int, string>
     */
    public static function regions(): array
    {
        return once(static fn (): array => Region::query()->orderBy('name')->get(['id', 'code', 'name'])->mapWithKeys(static fn (Region $region): array => [$region->id => $region->name.' ('.$region->code.')'])->all());
    }

    /**
     * @return array<int, string>
     */
    public static function deliveryFields(): array
    {
        return once(static fn (): array => DeliveryFieldDefinition::query()->orderBy('label')->get(['id', 'code', 'label'])->mapWithKeys(static fn (DeliveryFieldDefinition $field): array => [$field->id => $field->label.' ('.$field->code.')'])->all());
    }

    /**
     * Currencies by code, active first.
     *
     * @return array<string, string>
     */
    public static function currencies(bool $activeOnly = false): array
    {
        return once(static fn (): array => Currency::query()
            ->when($activeOnly, static fn ($query) => $query->where('is_active', true))
            ->orderByDesc('is_active')->orderBy('sort_order')->orderBy('code')
            ->get(['code', 'name'])->mapWithKeys(static fn (Currency $currency): array => [$currency->code => $currency->code.' · '.$currency->name])->all());
    }

    /**
     * @return array<string, string>
     */
    public static function countries(): array
    {
        return once(static fn (): array => Country::query()->orderBy('name')->get(['code', 'name'])->mapWithKeys(static fn (Country $country): array => [$country->code => $country->name.' ('.$country->code.')'])->all());
    }

    /**
     * @return array<int, string>
     */
    public static function faqGroups(): array
    {
        return once(static fn (): array => FaqGroup::query()->orderBy('sort_order')->pluck('name', 'id')->all());
    }

    /**
     * @return array<int, string>
     */
    public static function blogCategories(): array
    {
        return once(static fn (): array => BlogCategory::query()->orderBy('name')->pluck('name', 'id')->all());
    }

    /**
     * @return array<int, string>
     */
    public static function suppliers(): array
    {
        return once(static fn (): array => Supplier::query()->orderBy('priority')->pluck('name', 'id')->all());
    }
}
