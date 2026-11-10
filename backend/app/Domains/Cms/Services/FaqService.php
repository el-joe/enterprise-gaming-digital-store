<?php

namespace App\Domains\Cms\Services;

use App\Domains\Cms\Models\Faq;
use App\Domains\Shared\Services\CrudService;

/**
 * @extends CrudService<Faq>
 */
class FaqService extends CrudService
{
    protected function model(): string
    {
        return Faq::class;
    }

    protected function auditPrefix(): string
    {
        return 'cms.faq';
    }

    protected function filters(): array
    {
        return ['faq_group_id' => 'exact', 'is_active' => 'bool', 'show_on_homepage' => 'bool'];
    }

    protected function sorts(): array
    {
        return ['id', 'sort_order'];
    }

    protected function searchColumns(): array
    {
        return ['question'];
    }

    protected function defaultSort(): string
    {
        return 'sort_order';
    }
}
