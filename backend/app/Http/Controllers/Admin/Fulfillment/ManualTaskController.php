<?php

namespace App\Http\Controllers\Admin\Fulfillment;

use App\Domains\Fulfillment\Models\ManualFulfillmentTask;
use App\Domains\Fulfillment\Services\ManualTaskService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Fulfillment\ReasonRequest;
use App\Http\Resources\Admin\Fulfillment\ManualTaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManualTaskController extends Controller
{
    public function __construct(private ManualTaskService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), ManualTaskResource::class);
    }

    public function show(ManualFulfillmentTask $manualTask): JsonResponse
    {
        return $this->success(ManualTaskResource::make($this->service->forStaffView($manualTask))->resolve());
    }

    public function claim(Request $request, ManualFulfillmentTask $manualTask): JsonResponse
    {
        return $this->success(ManualTaskResource::make($this->service->claim($manualTask, $request->user()))->resolve(), 'Task claimed.');
    }

    public function complete(ReasonRequest $request, ManualFulfillmentTask $manualTask): JsonResponse
    {
        return $this->success(ManualTaskResource::make($this->service->complete($manualTask, $request->user(), $request->validated('reason')))->resolve(), 'Task completed.');
    }

    public function reject(ReasonRequest $request, ManualFulfillmentTask $manualTask): JsonResponse
    {
        return $this->success(ManualTaskResource::make($this->service->reject($manualTask, $request->user(), $request->validated('reason')))->resolve(), 'Task rejected.');
    }
}
