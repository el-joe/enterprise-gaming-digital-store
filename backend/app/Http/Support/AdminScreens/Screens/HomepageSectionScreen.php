<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Models\HomepageSection;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Homepage sections (spec Phase 9.8 and 9.9): order, source (manual items or an automatic rule) and settings. Manual items are on the edit page.
 */
class HomepageSectionScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'marketing';
    }

    public static function slug(): string
    {
        return 'homepage-sections';
    }

    public static function viewPermission(): string
    {
        return 'content.homepage';
    }

    public static function managePermission(): string
    {
        return 'content.homepage';
    }

    public static function model(): string
    {
        return HomepageSection::class;
    }

    public static function table(): string
    {
        return 'homepage-sections';
    }

    protected function apiPath(): string
    {
        return '/admin/api/homepage-sections';
    }

    public function translations(): ?array
    {
        return ['type' => 'homepage_section', 'fields' => (array) config('admin.translations.homepage_section.fields')];
    }

    public function panel(): ?string
    {
        return 'admin.marketing.homepage-items';
    }

    public function panelData(Model $record): array
    {
        return [
            'items' => $record->homepageSectionItems()->orderBy('sort_order')->get(['item_type', 'item_id'])
                ->map(static fn ($item): array => ['item_type' => $item->item_type, 'item_id' => (int) $item->item_id])->all(),
        ];
    }

    public function fields(?Model $record): array
    {
        return [
            'section' => [
                Field::text('key', $this->l('key'))->required()->max(64)->ltr(),
                Field::select('source', $this->l('source'), Options::enum('section_source', HomepageSectionSource::cases()))->required(),
                Field::text('title', $this->l('title'))->nullable()->max(190),
                Field::text('subtitle', $this->l('subtitle'))->nullable()->max(255),
                Field::number('sort_order', $this->l('sort_order'))->required()->default(0),
                Field::toggle('is_active', $this->l('is_active'))->default(true),
                Field::json('settings', $this->l('settings'))->nullable()->help(__('admin.help.settings_json')),
            ],
        ];
    }
}
