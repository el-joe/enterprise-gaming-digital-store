<?php

namespace App\Http\Controllers\Admin\Fulfillment;

use App\Domains\Fulfillment\Services\FulfilmentAdminService;
use App\Domains\Orders\Models\Order;
use App\Domains\Suppliers\Models\SupplierTransaction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Fulfillment\MarkNotAcceptedRequest;
use App\Http\Requests\Admin\Fulfillment\ReasonRequest;
use App\Http\Requests\Admin\Fulfillment\RejectOrderRequest;
use App\Http\Requests\Admin\Fulfillment\RetryUnitRequest;
use App\Http\Resources\Admin\Fulfillment\OrderEventResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderFulfilmentController extends Controller
{
    public function __construct(private FulfilmentAdminService $service) {}

    public function timeline(Order $order): JsonResponse
    {
        return $this->success(['events' => OrderEventResource::collection($this->service->timeline($order))->resolve()]);
    }

    public function review(ReasonRequest $request, Order $order): JsonResponse
    {
        $order = $this->service->approve($order, $request->user(), $request->validated('reason'));

        return $this->success(['public_id' => $order->public_id, 'status' => $order->status->value], 'Order released for fulfilment.');
    }

    /**
     * M14: reject an order held in review (cancel when unpaid, refund the undelivered units when
     * paid).
     */
    public function reject(RejectOrderRequest $request, Order $order): JsonResponse
    {
        $order = $this->service->reject($order, $request->user(), $request->validated('reason'), $request->refundReason());

        return $this->success(['public_id' => $order->public_id, 'status' => $order->status->value], 'Order rejected.');
    }

    public function retryVerifiedUnit(RetryUnitRequest $request, Order $order): JsonResponse
    {
        $order = $this->service->retryVerifiedUnit($order, $request->validated('unit_public_id'), $request->user(), $request->validated('reason'));

        return $this->success(['public_id' => $order->public_id, 'status' => $order->status->value], 'Unit queued for retry.');
    }

    public function reconcile(Request $request, Order $order): JsonResponse
    {
        return $this->success(['attempts_checked' => $this->service->reconcile($order, $request->user())], 'Verification run.');
    }

    public function markNotAccepted(MarkNotAcceptedRequest $request, SupplierTransaction $transaction): JsonResponse
    {
        $transaction = $this->service->markNotAccepted($transaction, $request->user(), $request->validated('reason'), $request->validated('evidence'));

        return $this->success(['public_id' => $transaction->public_id, 'status' => $transaction->status->value], 'Attempt marked verified not accepted.');
    }
}
