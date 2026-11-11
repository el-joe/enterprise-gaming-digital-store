<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Seo\Models\Redirect;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Redirects (spec Phase 9.9): 301, 302 or 410 for same-site paths only.
 */
class RedirectScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'content';
    }

    public static function slug(): string
    {
        return 'redirects';
    }

    public static function viewPermission(): string
    {
        return 'seo.redirects';
    }

    public static function managePermission(): string
    {
        return 'seo.redirects';
    }

    public static function model(): string
    {
        return Redirect::class;
    }

    public static function table(): string
    {
        return 'redirects';
    }

    protected function apiPath(): string
    {
        return '/admin/api/redirects';
    }

    public function fields(?Model $record): array
    {
        return [
            'redirect' => [
                Field::text('source_path', $this->l('source_path'))->required()->max(500)->ltr()->help(__('admin.help.path')),
                Field::text('target_path', $this->l('target_path'))->nullable()->max(500)->ltr()->help(__('admin.help.path')),
                Field::select('status_code', $this->l('status_code'), ['301' => '301', '302' => '302', '410' => '410'])->required(),
                Field::toggle('is_active', $this->l('is_active'))->default(true),
            ],
        ];
    }
}
