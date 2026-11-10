<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Payments\Services\RefundService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Payments\CreateRefundRequest;
use App\Http\Resources\Admin\Payments\RefundResource;
use Illuminate\Http\JsonResponse;

class RefundController extends Controller
{
    /**
     * Staff refund of chosen units, sent to the provider afterwards (status `requested`).
     */
    public function store(CreateRefundRequest $request, Order $order, RefundService $refunds): JsonResponse
    {
        $refund = $refunds->refundUnits(
            $order,
            $request->validated('unit_ids'),
            RefundReason::from($request->validated('reason')),
            OrderActor::staff($request->user()),
            $request->validated('note'),
        );

        return $this->success(RefundResource::make($refund->refresh())->resolve(), 'Refund requested.', status: 201);
    }
}
