<?php

namespace App\Http\Controllers\Mcp;

use App\Domains\Mcp\Repositories\McpOperationsRepository;
use App\Domains\Mcp\Services\McpOperationsReader;
use App\Http\Requests\Mcp\AnalyticsRangeRequest;
use App\Http\Requests\Mcp\McpPageRequest;
use App\Http\Requests\Mcp\SupplierFilterRequest;
use App\Http\Resources\Mcp\McpPaymentFailureResource;
use App\Http\Resources\Mcp\McpReconciliationItemResource;
use Illuminate\Http\JsonResponse;

class OperationsController extends McpController
{
    public function __construct(private McpOperationsReader $reader, private McpOperationsRepository $operations) {}

    public function supplierBalances(): JsonResponse
    {
        return $this->success($this->reader->supplierBalances());
    }

    public function supplierStatus(SupplierFilterRequest $request): JsonResponse
    {
        return $this->success($this->reader->supplierStatus($request->validated('supplier')));
    }

    public function supplierSyncStatus(SupplierFilterRequest $request): JsonResponse
    {
        return $this->success($this->reader->syncStatus($request->validated('supplier')));
    }

    public function failedTransactions(AnalyticsRangeRequest $request): JsonResponse
    {
        return $this->paginated($this->operations->failedPaymentsPage($request->from(), $request->to(), $request->page(), $request->perPage()), McpPaymentFailureResource::class);
    }

    public function pendingReconciliation(McpPageRequest $request): JsonResponse
    {
        return $this->paginated(
            $this->operations->openReconciliationPage($request->page(), $request->perPage()),
            McpReconciliationItemResource::class,
            ['unresolved_supplier_attempts' => $this->operations->unresolvedSupplierAttempts()],
        );
    }

    public function systemHealth(): JsonResponse
    {
        return $this->success($this->reader->systemHealth());
    }
}
