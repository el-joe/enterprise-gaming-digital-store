<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Cms\Enums\BannerPlacement;
use App\Domains\Cms\Models\Banner;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Homepage and section banners (spec Phase 9.8): placement, media, call to action and schedule.
 */
class BannerScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'marketing';
    }

    public static function slug(): string
    {
        return 'banners';
    }

    public static function viewPermission(): string
    {
        return 'marketing.banners';
    }

    public static function managePermission(): string
    {
        return 'marketing.banners';
    }

    public static function model(): string
    {
        return Banner::class;
    }

    public static function table(): string
    {
        return 'banners';
    }

    protected function apiPath(): string
    {
        return '/admin/api/banners';
    }

    public function translations(): ?array
    {
        return ['type' => 'banner', 'fields' => (array) config('admin.translations.banner.fields')];
    }

    public function fields(?Model $record): array
    {
        return [
            'banner' => [
                Field::select('placement', $this->l('placement'), Options::enum('banner_placement', BannerPlacement::cases()))->required(),
                Field::text('title', $this->l('title'))->nullable()->max(190),
                Field::text('subtitle', $this->l('subtitle'))->nullable()->max(255),
                Field::text('cta_label', $this->l('cta_label'))->nullable()->max(64),
                Field::text('cta_url', $this->l('cta_url'))->nullable()->max(500)->ltr()->help(__('admin.help.safe_url')),
                Field::image('media_id', $this->l('media_id'))->required()->help(__('admin.help.image_upload')),
                Field::image('mobile_media_id', $this->l('mobile_media_id'))->nullable()->help(__('admin.help.image_upload')),
            ],
            'schedule' => [
                Field::datetime('starts_at', $this->l('starts_at')),
                Field::datetime('ends_at', $this->l('ends_at')),
                Field::number('sort_order', $this->l('sort_order'))->default(0),
                Field::toggle('is_active', $this->l('is_active'))->default(true),
            ],
        ];
    }
}
