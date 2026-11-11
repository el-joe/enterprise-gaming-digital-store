<?php

namespace App\DataTables\Content;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Seo\Models\Redirect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Redirects (spec Phase 9.9).
 */
class RedirectsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'redirects';
    }

    public static function permission(): string
    {
        return 'seo.redirects';
    }

    public function columns(): array
    {
        return [
            new Column('source', __('admin.col.source'), 'redirects.source_path', html: true),
            new Column('target', __('admin.col.target'), html: true),
            new Column('status_code', __('admin.col.status_code'), 'redirects.status_code', numeric: true),
            new Column('hits', __('admin.col.hits'), 'redirects.hits', numeric: true),
            new Column('active', __('admin.col.active'), 'redirects.is_active', html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['source', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['redirects.source_path'];
    }

    protected function query(): Builder
    {
        return Redirect::query()->select('redirects.*');
    }

    /**
     * @param  Redirect  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.content.redirects.edit', ['record' => $row->getRouteKey()]);

        return [
            'source' => Cell::link($edit, (string) $row->source_path, true),
            'target' => Cell::mono($row->target_path),
            'status_code' => (string) $row->status_code,
            'hits' => (string) ($row->getAttribute('hits') ?? 0),
            'active' => Cell::yesNo((bool) $row->is_active),
        ];
    }
}
