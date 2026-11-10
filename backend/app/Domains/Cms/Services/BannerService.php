<?php

namespace App\Domains\Cms\Services;

use App\Domains\Cms\Models\Banner;
use App\Domains\Shared\Services\CrudService;

/**
 * @extends CrudService<Banner>
 */
class BannerService extends CrudService
{
    protected function model(): string
    {
        return Banner::class;
    }

    protected function auditPrefix(): string
    {
        return 'cms.banner';
    }

    protected function filters(): array
    {
        return ['placement' => 'exact', 'is_active' => 'bool'];
    }

    protected function sorts(): array
    {
        return ['id', 'sort_order', 'starts_at', 'ends_at'];
    }

    protected function searchColumns(): array
    {
        return ['title'];
    }

    protected function defaultSort(): string
    {
        return 'sort_order';
    }
}
