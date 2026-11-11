<?php

namespace App\DataTables\Content;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Cms\Enums\BlogPostStatus;
use App\Domains\Cms\Models\BlogPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Blog posts (spec Phase 9.9).
 */
class BlogPostsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'blog-posts';
    }

    public static function permission(): string
    {
        return 'content.blog';
    }

    public function columns(): array
    {
        return [
            new Column('title', __('admin.col.title'), 'blog_posts.title', html: true),
            new Column('category', __('admin.col.category'), html: true),
            new Column('status', __('admin.col.status'), 'blog_posts.status', html: true),
            new Column('published', __('admin.col.published'), 'blog_posts.published_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['published', 'desc'];
    }

    protected function searchColumns(): array
    {
        return ['blog_posts.title', 'blog_posts.slug'];
    }

    public function filters(): array
    {
        return [new Filter('status', __('admin.col.status'), 'select', static function (Builder $query, string $value): void {
            $query->where('blog_posts.status', $value);
        }, $this->enumOptions('blog_status', BlogPostStatus::cases()))];
    }

    protected function query(): Builder
    {
        return BlogPost::query()->select(['blog_posts.id', 'blog_posts.title', 'blog_posts.slug', 'blog_posts.status', 'blog_posts.published_at', 'blog_posts.blog_category_id'])->with('blogCategory:id,name');
    }

    /**
     * @param  BlogPost  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.content.blog-posts.edit', ['record' => $row->getRouteKey()]);

        return [
            'title' => Cell::link($edit, (string) $row->title),
            'category' => e((string) ($row->blogCategory?->name ?? '—')),
            'status' => Cell::status('blog_status', $row->status?->value),
            'published' => $this->dateTime($row->published_at),
        ];
    }
}
