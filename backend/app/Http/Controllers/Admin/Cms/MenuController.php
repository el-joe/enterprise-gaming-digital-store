<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Domains\Cms\Models\Menu;
use App\Domains\Cms\Services\MenuService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Cms\SaveMenuRequest;
use App\Http\Resources\Admin\Cms\MenuResource;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __construct(private MenuService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), MenuResource::class);
    }

    public function store(SaveMenuRequest $request): JsonResponse
    {
        return $this->success(MenuResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Menu $menu): JsonResponse
    {
        return $this->success(MenuResource::make($this->service->withRelations($menu))->resolve());
    }

    public function update(SaveMenuRequest $request, Menu $menu): JsonResponse
    {
        return $this->success(MenuResource::make($this->service->update($menu, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $this->service->delete($menu);

        return $this->success(message: 'Deleted.');
    }
}
