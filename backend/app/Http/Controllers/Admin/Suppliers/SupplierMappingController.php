<?php

namespace App\Http\Controllers\Admin\Suppliers;

use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Services\SupplierMappingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Suppliers\SaveSupplierMappingRequest;
use App\Http\Resources\Admin\Suppliers\SupplierMappingResource;
use Illuminate\Http\JsonResponse;

class SupplierMappingController extends Controller
{
    public function __construct(private SupplierMappingService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), SupplierMappingResource::class);
    }

    public function store(SaveSupplierMappingRequest $request): JsonResponse
    {
        return $this->success(SupplierMappingResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(SupplierProductMapping $supplierMapping): JsonResponse
    {
        return $this->success(SupplierMappingResource::make($this->service->withRelations($supplierMapping))->resolve());
    }

    public function update(SaveSupplierMappingRequest $request, SupplierProductMapping $supplierMapping): JsonResponse
    {
        return $this->success(SupplierMappingResource::make($this->service->update($supplierMapping, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(SupplierProductMapping $supplierMapping): JsonResponse
    {
        $this->service->delete($supplierMapping);

        return $this->success(message: 'Deleted.');
    }
}
