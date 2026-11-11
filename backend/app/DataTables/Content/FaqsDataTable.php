<?php

namespace App\DataTables\Content;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Cms\Models\Faq;
use App\Http\Support\AdminScreens\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * FAQs (spec Phase 9.9).
 */
class FaqsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'faqs';
    }

    public static function permission(): string
    {
        return 'content.faq';
    }

    public function columns(): array
    {
        return [
            new Column('question', __('admin.col.question'), 'faqs.question', html: true),
            new Column('group', __('admin.col.group'), html: true),
            new Column('homepage', __('admin.col.homepage'), 'faqs.show_on_homepage', html: true),
            new Column('sort_order', __('admin.col.sort_order'), 'faqs.sort_order', numeric: true),
            new Column('active', __('admin.col.active'), 'faqs.is_active', html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['sort_order', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['faqs.question'];
    }

    public function filters(): array
    {
        return [
            new Filter('group', __('admin.col.group'), 'select', static function (Builder $query, string $value): void {
                $query->where('faqs.faq_group_id', (int) $value);
            }, array_map('strval', Options::faqGroups())),
            $this->booleanFilter('active', __('admin.col.active'), 'faqs.is_active'),
        ];
    }

    protected function query(): Builder
    {
        return Faq::query()->select('faqs.*')->with('faqGroup:id,name');
    }

    /**
     * @param  Faq  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.content.faqs.edit', ['record' => $row->getRouteKey()]);

        return [
            'question' => Cell::link($edit, Str::limit((string) $row->question, 90)),
            'group' => e((string) ($row->faqGroup?->name ?? '—')),
            'homepage' => Cell::yesNo((bool) $row->show_on_homepage),
            'sort_order' => (string) $row->sort_order,
            'active' => Cell::yesNo((bool) $row->is_active),
        ];
    }
}
