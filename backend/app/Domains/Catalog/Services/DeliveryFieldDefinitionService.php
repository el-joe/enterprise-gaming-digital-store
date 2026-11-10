<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Shared\Services\CrudService;

/**
 * @extends CrudService<DeliveryFieldDefinition>
 */
class DeliveryFieldDefinitionService extends CrudService
{
    protected function model(): string
    {
        return DeliveryFieldDefinition::class;
    }

    protected function auditPrefix(): string
    {
        return 'catalog.delivery_field';
    }

    protected function filters(): array
    {
        return ['input_type' => 'exact'];
    }

    protected function sorts(): array
    {
        return ['id', 'code', 'label'];
    }

    protected function searchColumns(): array
    {
        return ['code', 'label'];
    }

    protected function defaultSort(): string
    {
        return 'label';
    }
}
