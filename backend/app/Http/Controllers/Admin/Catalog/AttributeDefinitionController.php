<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domains\Catalog\Models\AttributeDefinition;
use App\Domains\Catalog\Services\AttributeDefinitionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Catalog\SaveAttributeDefinitionRequest;
use App\Http\Resources\Admin\Catalog\AttributeDefinitionResource;
use Illuminate\Http\JsonResponse;

class AttributeDefinitionController extends Controller
{
    public function __construct(private AttributeDefinitionService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), AttributeDefinitionResource::class);
    }

    public function store(SaveAttributeDefinitionRequest $request): JsonResponse
    {
        return $this->success(AttributeDefinitionResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(AttributeDefinition $attributeDefinition): JsonResponse
    {
        return $this->success(AttributeDefinitionResource::make($this->service->withRelations($attributeDefinition))->resolve());
    }

    public function update(SaveAttributeDefinitionRequest $request, AttributeDefinition $attributeDefinition): JsonResponse
    {
        return $this->success(AttributeDefinitionResource::make($this->service->update($attributeDefinition, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(AttributeDefinition $attributeDefinition): JsonResponse
    {
        $this->service->delete($attributeDefinition);

        return $this->success(message: 'Deleted.');
    }
}
