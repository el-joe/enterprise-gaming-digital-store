<?php

namespace App\DataTables\Marketing;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Cms\Models\HomepageSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Homepage sections (spec Phase 9.9).
 */
class HomepageSectionsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'homepage-sections';
    }

    public static function permission(): string
    {
        return 'content.homepage';
    }

    public function columns(): array
    {
        return [
            new Column('key', __('admin.col.key'), 'homepage_sections.key', html: true),
            new Column('title', __('admin.col.title'), 'homepage_sections.title'),
            new Column('source', __('admin.col.source'), 'homepage_sections.source'),
            new Column('items', __('admin.col.items'), 'items_count', numeric: true),
            new Column('sort_order', __('admin.col.sort_order'), 'homepage_sections.sort_order', numeric: true),
            new Column('active', __('admin.col.active'), 'homepage_sections.is_active', html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['sort_order', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['homepage_sections.key', 'homepage_sections.title'];
    }

    protected function query(): Builder
    {
        return HomepageSection::query()->select('homepage_sections.*')->withCount('homepageSectionItems as items_count');
    }

    /**
     * @param  HomepageSection  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.marketing.homepage-sections.edit', ['record' => $row->getRouteKey()]);

        return [
            'key' => Cell::link($edit, (string) $row->key, true),
            'title' => (string) ($row->title ?? '—'),
            'source' => Cell::label('section_source', $row->source?->value),
            'items' => (string) $row->getAttribute('items_count'),
            'sort_order' => (string) $row->sort_order,
            'active' => Cell::yesNo((bool) $row->is_active),
        ];
    }
}
