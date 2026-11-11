<?php

namespace App\DataTables\Content;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Cms\Models\Media;
use App\Domains\Cms\Services\MediaUrls;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The media library (spec Phase 9.9): re-encoded images with their id for use in forms, alt text and delete for unused files.
 */
class MediaDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'media';
    }

    public static function permission(): string
    {
        return 'content.media';
    }

    public function columns(): array
    {
        return [
            new Column('preview', __('admin.col.preview'), html: true),
            new Column('id', __('admin.col.id'), 'media.id', numeric: true),
            new Column('file', __('admin.col.file'), 'media.original_filename', html: true),
            new Column('size', __('admin.col.size'), html: true),
            new Column('alt', __('admin.col.alt'), 'media.alt'),
            new Column('folder', __('admin.col.folder'), 'media.folder'),
            new Column('created', __('admin.col.created'), 'media.created_at'),
            new Column('actions', __('admin.col.actions'), html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['created', 'desc'];
    }

    protected function searchColumns(): array
    {
        return ['media.original_filename', 'media.folder', 'media.alt'];
    }

    protected function query(): Builder
    {
        return Media::query()->select('media.*');
    }

    /**
     * @param  Media  $row
     */
    protected function cells(Model $row): array
    {
        $url = app(MediaUrls::class)->url((string) $row->disk, (string) $row->path);
        $manage = $this->viewer->can('content.media');

        return [
            'preview' => '<img src="'.e($url).'" alt="" loading="lazy" class="size-10 rounded-sm bg-surface-sunken object-cover">',
            'id' => (string) $row->id,
            'file' => Cell::stack((string) $row->original_filename, (string) $row->mime),
            'size' => Cell::mono(($row->width && $row->height ? $row->width.'×'.$row->height.' · ' : '').number_format((int) $row->size_bytes / 1024, 0).' KB'),
            'alt' => (string) ($row->alt ?? '—'),
            'folder' => (string) ($row->folder ?? '—'),
            'created' => $this->dateTime($row->created_at),
            'actions' => $manage ? Cell::action(__('admin.common.delete'), url('/admin/api/media/'.$row->getRouteKey()), 'DELETE', confirm: __('admin.media.delete_confirm'), danger: true) : '',
        ];
    }
}
