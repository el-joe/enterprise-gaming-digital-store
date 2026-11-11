<?php

namespace App\Http\Controllers\Admin\Tables;

use App\Domains\Fulfillment\Services\FulfilmentAdminService;
use App\Domains\Payments\Enums\ReconciliationItemStatus;
use App\Domains\Payments\Services\PaymentGatewayAdminService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tables\BulkIdsRequest;
use Illuminate\Http\JsonResponse;

/**
 * Bulk actions on selected table rows (M14). Each route has the permission of the action; each
 * row is processed by the same service as the single action and audited per row.
 */
class BulkActionController extends Controller
{
    public function reconcileOrders(BulkIdsRequest $request, FulfilmentAdminService $service): JsonResponse
    {
        $result = $service->reconcileMany($request->validated('ids'), $request->user());

        return $this->success($result, 'Verification run.');
    }

    public function resolveReconciliation(BulkIdsRequest $request, PaymentGatewayAdminService $service): JsonResponse
    {
        return $this->closeItems($request, $service, ReconciliationItemStatus::Resolved);
    }

    public function ignoreReconciliation(BulkIdsRequest $request, PaymentGatewayAdminService $service): JsonResponse
    {
        return $this->closeItems($request, $service, ReconciliationItemStatus::Ignored);
    }

    private function closeItems(BulkIdsRequest $request, PaymentGatewayAdminService $service, ReconciliationItemStatus $status): JsonResponse
    {
        $ids = array_values(array_filter(array_map('intval', $request->validated('ids')), static fn (int $id): bool => $id > 0));
        $changed = $service->resolveItems($ids, $status, (string) $request->validated('note'), $request->user());

        return $this->success(['changed' => $changed], 'Saved.');
    }
}
