<?php

namespace App\DataTables\Content;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Cms\Models\BlogCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Blog categories (spec Phase 9.9).
 */
class BlogCategoriesDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'blog-categories';
    }

    public static function permission(): string
    {
        return 'content.blog';
    }

    public function columns(): array
    {
        return [
            new Column('name', __('admin.col.name'), 'blog_categories.name', html: true),
            new Column('slug', __('admin.col.slug'), 'blog_categories.slug', html: true),
            new Column('posts', __('admin.col.posts'), 'posts_count', numeric: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['name', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['blog_categories.name'];
    }

    protected function query(): Builder
    {
        return BlogCategory::query()->select('blog_categories.*')->withCount('blogPosts as posts_count');
    }

    /**
     * @param  BlogCategory  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.content.blog-categories.edit', ['record' => $row->getRouteKey()]);

        return [
            'name' => Cell::link($edit, (string) $row->name),
            'slug' => Cell::mono($row->slug),
            'posts' => (string) $row->getAttribute('posts_count'),
        ];
    }
}
