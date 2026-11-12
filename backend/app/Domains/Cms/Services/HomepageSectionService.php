<?php

namespace App\Domains\Cms\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Services\ContentTranslationAdminService;
use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @extends CrudService<HomepageSection>
 */
class HomepageSectionService extends CrudService
{
    public function __construct(ListQueryApplier $lists, AuditLogger $audit, private ContentTranslationAdminService $translations)
    {
        parent::__construct($lists, $audit);
    }

    /**
     * Replace the manually chosen items of a section, in the given order.
     *
     * @param  list<array{item_type: string, item_id: int}>  $items
     */
    public function replaceItems(HomepageSection $section, array $items): HomepageSection
    {
        DB::transaction(function () use ($section, $items): void {
            $section->homepageSectionItems()->delete();

            foreach (array_values($items) as $position => $item) {
                $section->homepageSectionItems()->create([...$item, 'sort_order' => $position]);
            }

            $this->audit->record(new AuditEntry(action: 'cms.homepage_section.items_replaced', subject: $section, after: ['items' => $items]));
        });

        return $this->withRelations($section->refresh());
    }

    /**
     * Storefront v2 drawer: a generated key (from the title) and a position at the end when left
     * out on create, and the source from the rule (a manual pick is `manual`).
     *
     * @param  HomepageSection  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        if (! $model->exists && empty($attributes['key'])) {
            $attributes['key'] = Str::limit(Str::slug((string) ($attributes['title'] ?? 'section'), '_') ?: 'section', 50, '').'_'.Str::lower(Str::random(6));
        }

        if (! $model->exists && ! array_key_exists('sort_order', $attributes)) {
            $attributes['sort_order'] = (int) HomepageSection::query()->max('sort_order') + 10;
        }

        if (is_array($attributes['settings'] ?? null)) {
            $attributes['settings'] = array_filter($attributes['settings'], static fn (mixed $value): bool => $value !== null && $value !== '');

            if (($attributes['settings']['rule'] ?? null) === 'manual') {
                unset($attributes['settings']['rule']);
                $attributes['source'] = HomepageSectionSource::Manual->value;
            }
        }

        return $attributes;
    }

    /**
     * The Arabic title goes to `content_translations` (audited by the translation service).
     *
     * @param  HomepageSection  $model
     */
    protected function afterSave(Model $model, array $attributes): void
    {
        if (array_key_exists('title_ar', $attributes)) {
            $this->translations->save($model, 'ar', ['title' => $attributes['title_ar']]);
        }
    }

    protected function model(): string
    {
        return HomepageSection::class;
    }

    protected function auditPrefix(): string
    {
        return 'cms.homepage_section';
    }

    protected function filters(): array
    {
        return ['is_active' => 'bool', 'source' => 'exact'];
    }

    protected function sorts(): array
    {
        return ['id', 'sort_order', 'key'];
    }

    protected function searchColumns(): array
    {
        return ['key', 'title'];
    }

    protected function relations(): array
    {
        return ['homepageSectionItems'];
    }

    protected function defaultSort(): string
    {
        return 'sort_order';
    }
}
