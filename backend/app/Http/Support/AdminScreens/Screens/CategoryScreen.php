<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Services\StorefrontNavigationService;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Suppliers\Services\SupplierImageCandidates;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Categories (spec Phase 9.2), with a parent for nesting.
 */
class CategoryScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'catalog';
    }

    public static function slug(): string
    {
        return 'categories';
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
        return Category::class;
    }

    public static function table(): string
    {
        return 'categories';
    }

    protected function apiPath(): string
    {
        return '/admin/api/categories';
    }

    public function translations(): ?array
    {
        return ['type' => 'category', 'fields' => (array) config('admin.translations.category.fields')];
    }

    public function seoType(): ?string
    {
        return 'category';
    }

    /**
     * Supplier images gallery (owner request 2026-11-12) beside the image field.
     */
    public function panel(): ?string
    {
        return 'admin.partials.supplier-images-panel';
    }

    public function panelData(Model $record): array
    {
        $candidates = app(SupplierImageCandidates::class);

        return ['candidates' => $candidates->forSubject($record), 'active_media_id' => $candidates->activeMediaId($record)];
    }

    public function fields(?Model $record): array
    {
        $parents = Options::categories();

        if ($record !== null) {
            unset($parents[$record->getKey()]);
        }

        return [
            'basics' => [
                Field::text('name', $this->l('name'))->required()->max(120),
                Field::text('slug', $this->l('slug'))->required()->max(140)->ltr()->help(__('admin.help.slug')),
                Field::select('parent_id', $this->l('parent_id'), $parents)->nullable(),
                Field::textarea('description', $this->l('description'))->nullable(),
            ],
            'display' => [
                Field::image('banner_media_id', $this->l('banner_media_id'))->nullable()->help(__('admin.help.image_upload')),
                Field::number('sort_order', $this->l('sort_order'))->default(0),
                Field::toggle('is_active', $this->l('is_active'))->default(true),
            ],
            // Storefront v2 (Figma 117:11830): header row and home page rail.
            'placement' => [
                Field::toggle('featured_in_header', $this->l('featured_in_header'))->default(false)->wide(),
                Field::number('header_order', $this->l('header_order'))->default(0)->wide()->help((string) __('admin.storefront.header_help', ['used' => $this->featuredCount(), 'max' => StorefrontNavigationService::MAX_FEATURED])),
                Field::toggle('show_on_home', $this->l('show_on_home'))->default(false)->wide(),
                Field::number('home_position', $this->l('home_position'))->default(0)->help((string) __('admin.storefront.home_position_help')),
                Field::number('home_limit', $this->l('home_limit'))->default(12)->help((string) __('admin.storefront.home_limit_help')),
            ],
        ];
    }

    public function asideGroups(): array
    {
        return ['placement'];
    }

    public function asideNote(): ?string
    {
        return 'admin.catalog.category-placement';
    }

    /**
     * The computed "See all" path and the home order: managed sections, then the category rails.
     */
    public function asideData(?Model $record): array
    {
        $managed = HomepageSection::query()->where('is_active', true)->count();
        $rails = Category::query()->where('is_active', true)->where('show_on_home', true)->orderBy('home_position')->orderBy('id')->get(['id', 'name']);

        return [
            'see_all' => $record === null ? '/c/{slug}' : '/c/'.$record->getAttribute('slug'),
            'managed' => $managed,
            'rails' => $rails->map(static fn (Category $rail): array => ['id' => $rail->id, 'name' => $rail->name])->all(),
            'current' => $record?->getKey(),
        ];
    }

    private function featuredCount(): int
    {
        return Category::query()->where('is_active', true)->where('featured_in_header', true)->count();
    }
}
