<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Domains\Cms\Models\FaqGroup;
use App\Domains\Cms\Services\FaqGroupService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Cms\SaveFaqGroupRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Resources\Admin\Cms\FaqGroupResource;
use Illuminate\Http\JsonResponse;

class FaqGroupController extends Controller
{
    public function __construct(private FaqGroupService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), FaqGroupResource::class);
    }

    public function store(SaveFaqGroupRequest $request): JsonResponse
    {
        return $this->success(FaqGroupResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(FaqGroup $faqGroup): JsonResponse
    {
        return $this->success(FaqGroupResource::make($this->service->withRelations($faqGroup))->resolve());
    }

    public function update(SaveFaqGroupRequest $request, FaqGroup $faqGroup): JsonResponse
    {
        return $this->success(FaqGroupResource::make($this->service->update($faqGroup, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(FaqGroup $faqGroup): JsonResponse
    {
        $this->service->delete($faqGroup);

        return $this->success(message: 'Deleted.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->ids());

        return $this->success(message: 'Reordered.');
    }
}
