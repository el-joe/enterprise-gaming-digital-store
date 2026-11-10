<?php

namespace App\Domains\Cms\Services;

use App\Domains\Cms\Models\BlogCategory;
use App\Domains\Shared\Services\CrudService;

/**
 * @extends CrudService<BlogCategory>
 */
class BlogCategoryService extends CrudService
{
    protected function model(): string
    {
        return BlogCategory::class;
    }

    protected function auditPrefix(): string
    {
        return 'cms.blog_category';
    }

    protected function filters(): array
    {
        return [];
    }

    protected function sorts(): array
    {
        return ['id', 'name'];
    }

    protected function searchColumns(): array
    {
        return ['name', 'slug'];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }
}
