<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Cms\Models\BlogCategory;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Blog categories (spec Phase 9.9).
 */
class BlogCategoryScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'content';
    }

    public static function slug(): string
    {
        return 'blog-categories';
    }

    public static function viewPermission(): string
    {
        return 'content.blog';
    }

    public static function managePermission(): string
    {
        return 'content.blog';
    }

    public static function model(): string
    {
        return BlogCategory::class;
    }

    public static function table(): string
    {
        return 'blog-categories';
    }

    protected function apiPath(): string
    {
        return '/admin/api/blog-categories';
    }

    public function fields(?Model $record): array
    {
        return [
            'basics' => [
                Field::text('name', $this->l('name'))->required()->max(120),
                Field::text('slug', $this->l('slug'))->required()->max(140)->ltr()->help(__('admin.help.slug')),
            ],
        ];
    }
}
