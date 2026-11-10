<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Jobs\ReindexCatalogScope;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends CrudService<GamingPlatform>
 */
class GamingPlatformService extends CrudService
{
    public function __construct(ListQueryApplier $lists, AuditLogger $audit, private SearchIndexer $search)
    {
        parent::__construct($lists, $audit);
    }

    protected function model(): string
    {
        return GamingPlatform::class;
    }

    protected function auditPrefix(): string
    {
        return 'catalog.platform';
    }

    protected function filters(): array
    {
        return ['is_active' => 'bool', 'is_featured' => 'bool'];
    }

    protected function sorts(): array
    {
        return ['id', 'name', 'sort_order', 'popularity_score', 'created_at'];
    }

    protected function searchColumns(): array
    {
        return ['name', 'slug'];
    }

    protected function defaultSort(): string
    {
        return 'sort_order';
    }

    /**
     * @param  GamingPlatform  $model
     */
    protected function afterSave(Model $model, array $attributes): void
    {
        $this->search->indexPlatform($model);

        if ($model->wasChanged('name')) {
            ReindexCatalogScope::dispatch('platform', $model->id)->afterCommit();
        }
    }

    /**
     * @param  GamingPlatform  $model
     */
    protected function guardDelete(Model $model): void
    {
        if ($model->products()->exists()) {
            throw $this->inUse('This platform still has products.');
        }
    }

    /**
     * @param  GamingPlatform  $model
     */
    protected function afterDelete(Model $model): void
    {
        $this->search->remove($model);
    }
}
