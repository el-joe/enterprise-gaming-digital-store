<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Suppliers\Services\SupplierImageCandidates;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Gaming platforms (spec Phase 9.2).
 */
class PlatformScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'catalog';
    }

    public static function slug(): string
    {
        return 'platforms';
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
        return GamingPlatform::class;
    }

    public static function table(): string
    {
        return 'platforms';
    }

    protected function apiPath(): string
    {
        return '/admin/api/platforms';
    }

    public function translations(): ?array
    {
        return ['type' => 'gaming_platform', 'fields' => (array) config('admin.translations.gaming_platform.fields')];
    }

    public function seoType(): ?string
    {
        return 'gaming_platform';
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
        return [
            'basics' => [
                Field::text('name', $this->l('name'))->required()->max(120),
                Field::text('slug', $this->l('slug'))->required()->max(140)->ltr()->help(__('admin.help.slug')),
                Field::textarea('description', $this->l('description'))->nullable(),
            ],
            'display' => [
                Field::image('logo_media_id', $this->l('logo_media_id'))->nullable()->help(__('admin.help.image_upload')),
                Field::image('banner_media_id', $this->l('banner_media_id'))->nullable()->help(__('admin.help.image_upload')),
                Field::number('popularity_score', $this->l('popularity_score'))->default(0),
                Field::number('sort_order', $this->l('sort_order'))->default(0),
                Field::toggle('is_active', $this->l('is_active'))->default(true),
                Field::toggle('is_featured', $this->l('is_featured')),
            ],
        ];
    }
}
