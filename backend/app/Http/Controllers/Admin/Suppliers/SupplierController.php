<?php

namespace App\Http\Controllers\Admin\Suppliers;

use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Suppliers\Jobs\SyncSupplierCatalog;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Services\SupplierAdminService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Suppliers\SaveSupplierRequest;
use App\Http\Requests\Admin\Suppliers\SetSupplierCredentialsRequest;
use App\Http\Resources\Admin\Suppliers\SupplierApiLogResource;
use App\Http\Resources\Admin\Suppliers\SupplierResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private SupplierAdminService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), SupplierResource::class);
    }

    public function store(SaveSupplierRequest $request): JsonResponse
    {
        return $this->success($this->present($this->service->create($request->validated())), 'Created.', status: 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return $this->success($this->present($supplier));
    }

    public function update(SaveSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        return $this->success($this->present($this->service->update($supplier, $request->validated())), 'Saved.');
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->service->delete($supplier);

        return $this->success(message: 'Deleted.');
    }

    /**
     * Write-only: the response says which modes are configured, never the values.
     */
    public function credentials(SetSupplierCredentialsRequest $request, Supplier $supplier): JsonResponse
    {
        $this->service->setCredentials($supplier, EnvironmentMode::from($request->validated('mode')), $request->validated('credentials'), $request->validated('webhook_secret'));

        return $this->success(['credentials_configured' => $this->service->credentialState($supplier)], 'Credentials saved.');
    }

    public function testConnection(Supplier $supplier): JsonResponse
    {
        return $this->success($this->service->testConnection($supplier));
    }

    public function capabilities(Supplier $supplier): JsonResponse
    {
        return $this->success($this->service->capabilities($supplier));
    }

    public function balance(Supplier $supplier): JsonResponse
    {
        return $this->success($this->service->balance($supplier));
    }

    public function logs(AdminListRequest $request, Supplier $supplier): JsonResponse
    {
        return $this->paginated($this->service->logs($supplier, $request->toListQuery()), SupplierApiLogResource::class);
    }

    public function syncCatalog(Request $request, Supplier $supplier): JsonResponse
    {
        SyncSupplierCatalog::dispatch($supplier->id, 'catalog', 'admin:'.$request->user()->id);

        return $this->success(message: 'Catalog sync queued.', status: 202);
    }

    public function syncPricing(Request $request, Supplier $supplier): JsonResponse
    {
        SyncSupplierCatalog::dispatch($supplier->id, 'pricing', 'admin:'.$request->user()->id);

        return $this->success(message: 'Pricing sync queued.', status: 202);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Supplier $supplier): array
    {
        return [...SupplierResource::make($supplier)->resolve(), 'credentials_configured' => $this->service->credentialState($supplier)];
    }
}
