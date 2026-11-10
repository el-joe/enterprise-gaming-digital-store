<?php

namespace App\Domains\Cms\Services;

use App\Domains\Cms\Models\Menu;
use App\Domains\Shared\Services\CrudService;

/**
 * @extends CrudService<Menu>
 */
class MenuService extends CrudService
{
    protected function model(): string
    {
        return Menu::class;
    }

    protected function auditPrefix(): string
    {
        return 'cms.menu';
    }

    protected function filters(): array
    {
        return [];
    }

    protected function sorts(): array
    {
        return ['id', 'location', 'name'];
    }

    protected function searchColumns(): array
    {
        return ['location', 'name'];
    }

    protected function defaultSort(): string
    {
        return 'location';
    }
}
