<?php

namespace App\Domains\Cms\Services;

use App\Domains\Cms\Models\Menu;
use App\Domains\Cms\Models\MenuItem;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Services\CrudService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * @extends CrudService<MenuItem>
 */
class MenuItemService extends CrudService
{
    public function listFor(Menu $menu, ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            MenuItem::query()->where('menu_id', $menu->id),
            $list,
            ['parent_id' => 'exact', 'is_active' => 'bool'],
            ['id', 'sort_order'],
            ['label'],
            'sort_order',
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createFor(Menu $menu, array $attributes): MenuItem
    {
        return $this->create([...$attributes, 'menu_id' => $menu->id]);
    }

    protected function model(): string
    {
        return MenuItem::class;
    }

    protected function auditPrefix(): string
    {
        return 'cms.menu_item';
    }

    /**
     * @param  MenuItem  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        $parentId = $attributes['parent_id'] ?? null;

        if ($parentId !== null) {
            $this->assertValidParent($model, (int) $parentId, (int) ($attributes['menu_id'] ?? $model->menu_id));
        }

        $state = [...$model->only(['url', 'linkable_type']), ...$attributes];

        if (empty($state['url']) === empty($state['linkable_type'])) {
            throw ValidationException::withMessages(['url' => ['Provide either a URL or a linked record, not both.']]);
        }

        return $attributes;
    }

    private function assertValidParent(MenuItem $item, int $parentId, int $menuId): void
    {
        $seen = [];
        $current = MenuItem::query()->find($parentId);

        if ($current === null || $current->menu_id !== $menuId) {
            throw ValidationException::withMessages(['parent_id' => ['The parent must belong to the same menu.']]);
        }

        while ($current !== null && ! in_array($current->id, $seen, true)) {
            if ($current->id === $item->id) {
                throw ValidationException::withMessages(['parent_id' => ['A menu item cannot be nested under itself.']]);
            }

            $seen[] = $current->id;
            $current = $current->parent_id === null ? null : MenuItem::query()->find($current->parent_id);
        }
    }
}
