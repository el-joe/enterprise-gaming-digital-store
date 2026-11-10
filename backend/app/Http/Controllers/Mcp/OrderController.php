<?php

namespace App\Http\Controllers\Mcp;

use App\Domains\Mcp\Repositories\McpOrderRepository;
use App\Domains\Mcp\Services\McpFormat;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Http\Requests\Mcp\AnalyticsRangeRequest;
use App\Http\Requests\Mcp\ListOrdersRequest;
use App\Http\Requests\Mcp\PendingOrdersRequest;
use App\Http\Requests\Mcp\SearchCustomerOrdersRequest;
use App\Http\Resources\Mcp\McpOrderDetailResource;
use App\Http\Resources\Mcp\McpOrderResource;
use Illuminate\Http\JsonResponse;

class OrderController extends McpController
{
    public function __construct(private McpOrderRepository $orders) {}

    public function index(ListOrdersRequest $request): JsonResponse
    {
        return $this->paginated($this->orders->ordersPage($request->statuses(), $request->from(), $request->to(), $request->page(), $request->perPage()), McpOrderResource::class);
    }

    public function search(SearchCustomerOrdersRequest $request): JsonResponse
    {
        return $this->paginated(
            $this->orders->customerOrdersPage($request->validated('email'), $request->validated('order_number'), $request->page(), $request->perPage()),
            McpOrderResource::class,
        );
    }

    public function show(string $orderNumber): JsonResponse
    {
        $order = $this->orders->findByNumber($orderNumber) ?? throw new ApplicationException('No order has this number.', 'not_found', 404);

        return $this->success(McpOrderDetailResource::make($order)->resolve());
    }

    public function status(string $orderNumber): JsonResponse
    {
        $order = $this->orders->findStatusByNumber($orderNumber) ?? throw new ApplicationException('No order has this number.', 'not_found', 404);

        return $this->success([
            'order_number' => $order->order_number,
            'status' => $order->status->value,
            'status_changed_at' => McpFormat::iso($order->status_changed_at),
            'is_final' => $order->status->isClosed(),
            'paid' => $order->paid_at !== null,
        ]);
    }

    public function failed(AnalyticsRangeRequest $request): JsonResponse
    {
        return $this->paginated(
            $this->orders->ordersPage([OrderStatus::Failed, OrderStatus::PartiallyFulfilled], $request->from(), $request->to(), $request->page(), $request->perPage()),
            McpOrderResource::class,
            ['statuses' => [OrderStatus::Failed->value, OrderStatus::PartiallyFulfilled->value]],
        );
    }

    public function pending(PendingOrdersRequest $request): JsonResponse
    {
        return $this->paginated(
            $this->orders->stalledOrdersPage($request->olderThanMinutes(), $request->page(), $request->perPage()),
            McpOrderResource::class,
            ['older_than_minutes' => $request->olderThanMinutes(), 'statuses' => array_map(static fn (OrderStatus $status): string => $status->value, McpOrderRepository::waitingStatuses())],
        );
    }
}
