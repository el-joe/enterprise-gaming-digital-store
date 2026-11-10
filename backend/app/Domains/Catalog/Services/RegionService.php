<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Models\Region;
use App\Domains\Shared\Services\CrudService;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends CrudService<Region>
 */
class RegionService extends CrudService
{
    protected function model(): string
    {
        return Region::class;
    }

    protected function auditPrefix(): string
    {
        return 'catalog.region';
    }

    protected function filters(): array
    {
        return ['is_global' => 'bool'];
    }

    protected function sorts(): array
    {
        return ['id', 'code', 'name'];
    }

    protected function searchColumns(): array
    {
        return ['code', 'name'];
    }

    protected function relations(): array
    {
        return ['countries'];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }

    /**
     * @param  Region  $model
     */
    protected function afterSave(Model $model, array $attributes): void
    {
        if (array_key_exists('country_codes', $attributes)) {
            $model->countries()->sync($attributes['country_codes']);
        }
    }
}
