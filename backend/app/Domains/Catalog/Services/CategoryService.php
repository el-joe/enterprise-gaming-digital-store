<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Jobs\ReindexCatalogScope;
use App\Domains\Catalog\Models\Category;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * @extends CrudService<Category>
 */
class CategoryService extends CrudService
{
    public function __construct(ListQueryApplier $lists, AuditLogger $audit, private SearchIndexer $search)
    {
        parent::__construct($lists, $audit);
    }

    protected function model(): string
    {
        return Category::class;
    }

    protected function auditPrefix(): string
    {
        return 'catalog.category';
    }

    protected function filters(): array
    {
        return ['is_active' => 'bool', 'parent_id' => 'exact'];
    }

    protected function sorts(): array
    {
        return ['id', 'name', 'sort_order', 'created_at'];
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
     * @param  Category  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        if ($model->exists && ! empty($attributes['parent_id'])) {
            $this->assertNotOwnAncestor($model, (int) $attributes['parent_id']);
        }

        return $attributes;
    }

    /**
     * @param  Category  $model
     */
    protected function afterSave(Model $model, array $attributes): void
    {
        $this->search->indexCategory($model);

        if ($model->wasChanged('name')) {
            ReindexCatalogScope::dispatch('category', $model->id)->afterCommit();
        }
    }

    /**
     * @param  Category  $model
     */
    protected function guardDelete(Model $model): void
    {
        if ($model->primaryCategoryProducts()->exists() || $model->products()->exists() || $model->children()->exists()) {
            throw $this->inUse('This category still has products or child categories.');
        }
    }

    /**
     * @param  Category  $model
     */
    protected function afterDelete(Model $model): void
    {
        $this->search->remove($model);
    }

    private function assertNotOwnAncestor(Category $category, int $parentId): void
    {
        $seen = [];
        $current = $parentId;

        while ($current !== null && ! in_array($current, $seen, true)) {
            if ($current === $category->id) {
                throw ValidationException::withMessages(['parent_id' => ['A category cannot be nested under itself.']]);
            }

            $seen[] = $current;
            $current = Category::query()->whereKey($current)->value('parent_id');
        }
    }
}
