<?php

namespace App\Http\Controllers\Account;

use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Repositories\CustomerOrderRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ListOrdersRequest;
use App\Http\Resources\Account\OrderDetailResource;
use App\Http\Resources\Account\OrderSummaryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * The customer's order history and order detail (M-A5, gap G4), for the web session and the
 * mobile bearer token alike (routes/account.php is registered for both). Ownership is the
 * OrderPolicy: another customer's order, and a guest order, are 404.
 */
class OrderController extends Controller
{
    public function __construct(private CustomerOrderRepository $orders) {}

    public function index(ListOrdersRequest $request): JsonResponse
    {
        return $this->paginated(
            $this->orders->paginateForCustomer($request->user()->id, $request->filter(), $request->page(), $request->perPage()),
            OrderSummaryResource::class,
        );
    }

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        return $this->success(['order' => OrderDetailResource::make($this->orders->loadDetail($order))->resolve()]);
    }
}
