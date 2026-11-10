<?php

namespace App\Domains\Cms\Services;

use App\Domains\Cms\Models\FaqGroup;
use App\Domains\Shared\Services\CrudService;

/**
 * @extends CrudService<FaqGroup>
 */
class FaqGroupService extends CrudService
{
    protected function model(): string
    {
        return FaqGroup::class;
    }

    protected function auditPrefix(): string
    {
        return 'cms.faq_group';
    }

    protected function filters(): array
    {
        return [];
    }

    protected function sorts(): array
    {
        return ['id', 'name', 'sort_order'];
    }

    protected function searchColumns(): array
    {
        return ['name', 'slug'];
    }

    protected function defaultSort(): string
    {
        return 'sort_order';
    }
}
