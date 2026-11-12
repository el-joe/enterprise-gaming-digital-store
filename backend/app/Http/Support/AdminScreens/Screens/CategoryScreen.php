<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Catalog\Models\Category;
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
        ];
    }
}
