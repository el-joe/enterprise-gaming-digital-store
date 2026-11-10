<?php

namespace App\Domains\Cms\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Shared\Services\CrudService;
use Illuminate\Support\Facades\DB;

/**
 * @extends CrudService<HomepageSection>
 */
class HomepageSectionService extends CrudService
{
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
