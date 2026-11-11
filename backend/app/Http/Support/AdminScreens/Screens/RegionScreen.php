<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Catalog\Models\Region;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Regions a variant is valid in (spec Phase 9.2), with their countries.
 */
class RegionScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'catalog';
    }

    public static function slug(): string
    {
        return 'regions';
    }

    public static function viewPermission(): string
    {
        return 'catalog.view';
    }

    public static function managePermission(): string
    {
        return 'catalog.manage';
    }

    public static function model(): string
    {
        return Region::class;
    }

    public static function table(): string
    {
        return 'regions';
    }

    protected function apiPath(): string
    {
        return '/admin/api/regions';
    }

    public function fields(?Model $record): array
    {
        return [
            'basics' => [
                Field::text('code', $this->l('code'))->required()->max(16)->ltr(),
                Field::text('name', $this->l('name'))->required()->max(80),
                Field::toggle('is_global', $this->l('is_global')),
                Field::multi('country_codes', $this->l('country_codes'), Options::countries())
                    ->using(static fn (Model $region): array => $region->countries()->pluck('countries.code')->all()),
            ],
        ];
    }
}
