<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Cms\Models\FaqGroup;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * FAQ groups (spec Phase 9.9).
 */
class FaqGroupScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'content';
    }

    public static function slug(): string
    {
        return 'faq-groups';
    }

    public static function viewPermission(): string
    {
        return 'content.faq';
    }

    public static function managePermission(): string
    {
        return 'content.faq';
    }

    public static function model(): string
    {
        return FaqGroup::class;
    }

    public static function table(): string
    {
        return 'faq-groups';
    }

    protected function apiPath(): string
    {
        return '/admin/api/faq-groups';
    }

    public function fields(?Model $record): array
    {
        return [
            'basics' => [
                Field::text('name', $this->l('name'))->required()->max(120),
                Field::text('slug', $this->l('slug'))->required()->max(140)->ltr()->help(__('admin.help.slug')),
                Field::number('sort_order', $this->l('sort_order'))->default(0),
            ],
        ];
    }
}
