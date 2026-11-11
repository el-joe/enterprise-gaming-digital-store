<?php

namespace App\DataTables\Content;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Cms\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * CMS pages (spec Phase 9.9).
 */
class PagesDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'pages';
    }

    public static function permission(): string
    {
        return 'content.pages';
    }

    public function columns(): array
    {
        return [
            new Column('title', __('admin.col.title'), 'pages.title', html: true),
            new Column('slug', __('admin.col.slug'), 'pages.slug', html: true),
            new Column('status', __('admin.col.status'), 'pages.status', html: true),
            new Column('updated', __('admin.col.updated'), 'pages.updated_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['updated', 'desc'];
    }

    protected function searchColumns(): array
    {
        return ['pages.title', 'pages.slug'];
    }

    protected function query(): Builder
    {
        return Page::query()->select(['pages.id', 'pages.title', 'pages.slug', 'pages.status', 'pages.updated_at']);
    }

    /**
     * @param  Page  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.content.pages.edit', ['record' => $row->getRouteKey()]);

        return [
            'title' => Cell::link($edit, (string) $row->title),
            'slug' => Cell::mono('/'.$row->slug),
            'status' => Cell::status('page_status', $row->status?->value),
            'updated' => $this->dateTime($row->updated_at),
        ];
    }
}
