<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Models\AttributeDefinition;
use App\Domains\Shared\Services\CrudService;

/**
 * @extends CrudService<AttributeDefinition>
 */
class AttributeDefinitionService extends CrudService
{
    protected function model(): string
    {
        return AttributeDefinition::class;
    }

    protected function auditPrefix(): string
    {
        return 'catalog.attribute';
    }

    protected function filters(): array
    {
        return ['type' => 'exact', 'is_filterable' => 'bool'];
    }

    protected function sorts(): array
    {
        return ['id', 'code', 'name'];
    }

    protected function searchColumns(): array
    {
        return ['code', 'name'];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }
}
