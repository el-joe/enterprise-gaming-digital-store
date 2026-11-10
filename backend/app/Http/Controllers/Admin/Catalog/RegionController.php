<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domains\Catalog\Models\Region;
use App\Domains\Catalog\Services\RegionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Catalog\SaveRegionRequest;
use App\Http\Resources\Admin\Catalog\RegionResource;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    public function __construct(private RegionService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), RegionResource::class);
    }

    public function store(SaveRegionRequest $request): JsonResponse
    {
        return $this->success(RegionResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Region $region): JsonResponse
    {
        return $this->success(RegionResource::make($this->service->withRelations($region))->resolve());
    }

    public function update(SaveRegionRequest $request, Region $region): JsonResponse
    {
        return $this->success(RegionResource::make($this->service->update($region, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Region $region): JsonResponse
    {
        $this->service->delete($region);

        return $this->success(message: 'Deleted.');
    }
}
