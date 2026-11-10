<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Services\DeliveryFieldDefinitionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Catalog\SaveDeliveryFieldDefinitionRequest;
use App\Http\Resources\Admin\Catalog\DeliveryFieldDefinitionResource;
use Illuminate\Http\JsonResponse;

class DeliveryFieldDefinitionController extends Controller
{
    public function __construct(private DeliveryFieldDefinitionService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), DeliveryFieldDefinitionResource::class);
    }

    public function store(SaveDeliveryFieldDefinitionRequest $request): JsonResponse
    {
        return $this->success(DeliveryFieldDefinitionResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(DeliveryFieldDefinition $deliveryFieldDefinition): JsonResponse
    {
        return $this->success(DeliveryFieldDefinitionResource::make($this->service->withRelations($deliveryFieldDefinition))->resolve());
    }

    public function update(SaveDeliveryFieldDefinitionRequest $request, DeliveryFieldDefinition $deliveryFieldDefinition): JsonResponse
    {
        return $this->success(DeliveryFieldDefinitionResource::make($this->service->update($deliveryFieldDefinition, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(DeliveryFieldDefinition $deliveryFieldDefinition): JsonResponse
    {
        $this->service->delete($deliveryFieldDefinition);

        return $this->success(message: 'Deleted.');
    }
}
