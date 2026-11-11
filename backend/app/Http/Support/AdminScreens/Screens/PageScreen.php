<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Cms\Enums\PageStatus;
use App\Domains\Cms\Models\Page;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * CMS pages including the legal pages (spec Phase 9.9). The body is sanitized HTML.
 */
class PageScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'content';
    }

    public static function slug(): string
    {
        return 'pages';
    }

    public static function viewPermission(): string
    {
        return 'content.pages';
    }

    public static function managePermission(): string
    {
        return 'content.pages';
    }

    public static function model(): string
    {
        return Page::class;
    }

    public static function table(): string
    {
        return 'pages';
    }

    protected function apiPath(): string
    {
        return '/admin/api/pages';
    }

    public function translations(): ?array
    {
        return ['type' => 'page', 'fields' => (array) config('admin.translations.page.fields')];
    }

    public function seoType(): ?string
    {
        return 'page';
    }

    public function fields(?Model $record): array
    {
        return [
            'page' => [
                Field::text('title', $this->l('title'))->required()->max(190),
                Field::text('slug', $this->l('slug'))->required()->max(210)->ltr()->help(__('admin.help.slug')),
                Field::select('status', $this->l('status'), Options::enum('page_status', PageStatus::cases()))->required(),
                Field::text('template', $this->l('template'))->nullable()->max(64)->ltr()->help(__('admin.help.template')),
                Field::html('body', $this->l('body'))->required()->help(__('admin.help.html')),
            ],
        ];
    }
}
