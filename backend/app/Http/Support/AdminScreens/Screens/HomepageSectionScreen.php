<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Cms\Enums\HomepageAutoRule;
use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Enums\HomepageSectionType;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Cms\Services\HomeSeeAllResolver;
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

    public function indexView(): ?string
    {
        return 'admin.marketing.homepage-sections';
    }

    /**
     * Every section in order with its type, English and Arabic title, rule, computed "See all"
     * and visibility; the category rails (read-only); the category and brand choices.
     */
    public function indexData(): array
    {
        $sections = HomepageSection::query()->withCount('homepageSectionItems')->with('homepageSectionItems')->orderBy('sort_order')->orderBy('id')->get();
        $arabic = ContentTranslation::query()
            ->where('translatable_type', (new HomepageSection)->getMorphClass())
            ->where('locale', 'ar')->where('field', 'title')
            ->pluck('value', 'translatable_id');
        $categories = Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->pluck('name', 'slug')->all();
        $brands = GamingPlatform::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->pluck('name', 'slug')->all();
        $seeAll = app(HomeSeeAllResolver::class);

        $rows = $sections->map(function (HomepageSection $section) use ($arabic, $seeAll, $categories, $brands): array {
            $type = HomepageSectionType::of($section);
            $settings = (array) ($section->settings ?? []);
            $rule = $section->source === HomepageSectionSource::Manual ? null : (HomepageAutoRule::tryFrom((string) ($settings['rule'] ?? '')) ?? HomepageAutoRule::defaultFor($section->key));
            $ruleKey = $type === HomepageSectionType::ProductRail && $section->source === HomepageSectionSource::Manual ? 'manual' : $rule?->value;

            return [
                'id' => $section->id,
                'key' => $section->key,
                'type' => $type->value,
                'editable' => in_array($type, HomepageSectionType::editable(), true),
                'source' => $section->source->value,
                'title' => (string) $section->title,
                'title_ar' => (string) ($arabic[$section->id] ?? ''),
                'default_title' => (string) __('storefront.home.sections.'.$section->key),
                'rule' => $ruleKey,
                'rule_label' => match ($ruleKey) {
                    null => (string) __('admin.storefront.items_value', ['count' => $section->homepage_section_items_count]),
                    'category' => (string) __('admin.storefront.category_value', ['name' => $categories[$settings['category'] ?? ''] ?? '—']),
                    'brand' => (string) __('admin.storefront.brand_value', ['name' => $brands[$settings['platform'] ?? ''] ?? '—']),
                    default => (string) __('admin.storefront.rules.'.$ruleKey),
                },
                'period' => (string) ($settings['period'] ?? 'all'),
                'limit' => (int) ($settings['limit'] ?? 12),
                'category' => (string) ($settings['category'] ?? ''),
                'platform' => (string) ($settings['platform'] ?? ''),
                'settings' => $settings,
                'see_all' => HomeSeeAllResolver::path($seeAll->forSection($section, $type, $rule)),
                'is_active' => $section->is_active,
                'sort_order' => $section->sort_order,
                'updated' => $section->updated_at?->diffForHumans(),
                'edit_url' => $this->editUrl($section),
            ];
        })->all();

        $rails = Category::query()->where('is_active', true)->where('show_on_home', true)->orderBy('home_position')->orderBy('id')->get();
        $railNames = ContentTranslation::query()
            ->where('translatable_type', (new Category)->getMorphClass())
            ->whereIn('translatable_id', $rails->modelKeys())
            ->where('locale', 'ar')->where('field', 'name')
            ->pluck('value', 'translatable_id');

        return [
            'sections' => $rows,
            'rails' => $rails->map(static fn (Category $category): array => [
                'name' => $category->name,
                'name_ar' => (string) ($railNames[$category->id] ?? ''),
                'position' => $category->home_position,
                'limit' => $category->home_limit,
                'see_all' => '/c/'.$category->slug,
                'edit_url' => route(CategoryScreen::routeName('edit'), ['record' => $category->getRouteKey()]),
            ])->all(),
            'categories' => $categories,
            'brands' => $brands,
            'apiUrl' => $this->apiUrl(),
            'canManage' => $this->canManage(),
            'previewUrl' => route('storefront.home'),
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
