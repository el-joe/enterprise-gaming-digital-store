<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Domains\Cms\Models\Page;
use App\Domains\Cms\Services\PageService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Cms\SavePageRequest;
use App\Http\Resources\Admin\Cms\PageResource;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function __construct(private PageService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), PageResource::class);
    }

    public function store(SavePageRequest $request): JsonResponse
    {
        return $this->success(PageResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Page $page): JsonResponse
    {
        return $this->success(PageResource::make($this->service->withRelations($page))->resolve());
    }

    public function update(SavePageRequest $request, Page $page): JsonResponse
    {
        return $this->success(PageResource::make($this->service->update($page, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->service->delete($page);

        return $this->success(message: 'Deleted.');
    }
}
