<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Domains\Cms\Models\Menu;
use App\Domains\Cms\Models\MenuItem;
use App\Domains\Cms\Services\MenuItemService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Cms\SaveMenuItemRequest;
use App\Http\Resources\Admin\Cms\MenuItemResource;
use Illuminate\Http\JsonResponse;

class MenuItemController extends Controller
{
    public function __construct(private MenuItemService $service) {}

    public function index(Menu $menu, AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->listFor($menu, $request->toListQuery()), MenuItemResource::class);
    }

    public function store(Menu $menu, SaveMenuItemRequest $request): JsonResponse
    {
        return $this->success(MenuItemResource::make($this->service->createFor($menu, $request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Menu $menu, MenuItem $menuItem): JsonResponse
    {
        return $this->success(MenuItemResource::make($this->service->withRelations($menuItem))->resolve());
    }

    public function update(Menu $menu, SaveMenuItemRequest $request, MenuItem $menuItem): JsonResponse
    {
        return $this->success(MenuItemResource::make($this->service->update($menuItem, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Menu $menu, MenuItem $menuItem): JsonResponse
    {
        $this->service->delete($menuItem);

        return $this->success(message: 'Deleted.');
    }
}
