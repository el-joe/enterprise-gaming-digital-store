<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Services\BannerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Cms\SaveBannerRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Resources\Admin\Cms\BannerResource;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    public function __construct(private BannerService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), BannerResource::class);
    }

    public function store(SaveBannerRequest $request): JsonResponse
    {
        return $this->success(BannerResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Banner $banner): JsonResponse
    {
        return $this->success(BannerResource::make($this->service->withRelations($banner))->resolve());
    }

    public function update(SaveBannerRequest $request, Banner $banner): JsonResponse
    {
        return $this->success(BannerResource::make($this->service->update($banner, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Banner $banner): JsonResponse
    {
        $this->service->delete($banner);

        return $this->success(message: 'Deleted.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->ids());

        return $this->success(message: 'Reordered.');
    }
}
