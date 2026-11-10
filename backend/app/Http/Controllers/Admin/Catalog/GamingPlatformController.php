<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Services\GamingPlatformService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Catalog\SaveGamingPlatformRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Resources\Admin\Catalog\GamingPlatformResource;
use Illuminate\Http\JsonResponse;

class GamingPlatformController extends Controller
{
    public function __construct(private GamingPlatformService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), GamingPlatformResource::class);
    }

    public function store(SaveGamingPlatformRequest $request): JsonResponse
    {
        return $this->success(GamingPlatformResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(GamingPlatform $gamingPlatform): JsonResponse
    {
        return $this->success(GamingPlatformResource::make($this->service->withRelations($gamingPlatform))->resolve());
    }

    public function update(SaveGamingPlatformRequest $request, GamingPlatform $gamingPlatform): JsonResponse
    {
        return $this->success(GamingPlatformResource::make($this->service->update($gamingPlatform, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(GamingPlatform $gamingPlatform): JsonResponse
    {
        $this->service->delete($gamingPlatform);

        return $this->success(message: 'Deleted.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->ids());

        return $this->success(message: 'Reordered.');
    }
}
