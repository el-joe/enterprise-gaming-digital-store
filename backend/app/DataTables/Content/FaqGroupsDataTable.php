<?php

namespace App\DataTables\Content;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Cms\Models\FaqGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * FAQ groups (spec Phase 9.9).
 */
class FaqGroupsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'faq-groups';
    }

    public static function permission(): string
    {
        return 'content.faq';
    }

    public function columns(): array
    {
        return [
            new Column('name', __('admin.col.name'), 'faq_groups.name', html: true),
            new Column('slug', __('admin.col.slug'), 'faq_groups.slug', html: true),
            new Column('faqs', __('admin.col.faqs'), 'faqs_count', numeric: true),
            new Column('sort_order', __('admin.col.sort_order'), 'faq_groups.sort_order', numeric: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['sort_order', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['faq_groups.name'];
    }

    protected function query(): Builder
    {
        return FaqGroup::query()->select('faq_groups.*')->withCount('faqs');
    }

    /**
     * @param  FaqGroup  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.content.faq-groups.edit', ['record' => $row->getRouteKey()]);

        return [
            'name' => Cell::link($edit, (string) $row->name),
            'slug' => Cell::mono($row->slug),
            'faqs' => (string) $row->getAttribute('faqs_count'),
            'sort_order' => (string) $row->sort_order,
        ];
    }
}
