<?php

namespace App\Http\Controllers\Admin\Promotions;

use App\Domains\Promotions\Models\Promotion;
use App\Domains\Promotions\Services\PromotionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Promotions\SavePromotionRequest;
use App\Http\Resources\Admin\Promotions\PromotionResource;
use Illuminate\Http\JsonResponse;

class PromotionController extends Controller
{
    public function __construct(private PromotionService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), PromotionResource::class);
    }

    public function store(SavePromotionRequest $request): JsonResponse
    {
        return $this->success(PromotionResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Promotion $promotion): JsonResponse
    {
        return $this->success(PromotionResource::make($this->service->withRelations($promotion))->resolve());
    }

    public function update(SavePromotionRequest $request, Promotion $promotion): JsonResponse
    {
        return $this->success(PromotionResource::make($this->service->update($promotion, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Promotion $promotion): JsonResponse
    {
        $this->service->delete($promotion);

        return $this->success(message: 'Deleted.');
    }
}
