<?php

namespace App\Http\Controllers\Web\Account;

use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Repositories\CustomerOrderRepository;
use App\Domains\Support\Repositories\SupportTicketRepository;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Account\Concerns\PresentsAccountData;
use App\Http\Requests\Web\Account\AccountPageRequest;
use App\Http\Requests\Web\Account\OrderListPageRequest;
use App\Http\Resources\Account\OrderDetailResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Account orders (M13): the history with status and period filters, and the order detail with
 * its timeline, payment, code reveal, payment retry and refund request. The detail is the page
 * the delivery emails link to (`/account/orders/{public_id}`). OrderPolicy: another customer's
 * order and any guest order are 404.
 */
class OrderController extends Controller
{
    use PresentsAccountData;

    public function __construct(private CustomerOrderRepository $orders) {}

    public function index(OrderListPageRequest $request): View
    {
        $page = $this->orders->paginateForCustomer($request->user()->id, $request->filter(), $request->page(), AccountPageRequest::PER_PAGE)
            ->withPath($request->url())
            ->appends(array_filter(['status' => $request->status(), 'period' => $request->period() === 'all' ? null : $request->period()]));

        return view('account.orders.index', [
            'orders' => $this->orderRows($page->getCollection(), $request),
            'paginator' => $page,
            'status' => $request->status(),
            'period' => $request->period(),
        ]);
    }

    public function show(Request $request, string $orderPublicId, SupportTicketRepository $tickets): Response
    {
        $order = $this->orders->findByPublicId($orderPublicId) ?? throw (new ModelNotFoundException)->setModel(Order::class);
        Gate::authorize('view', $order);

        $detail = (new OrderDetailResource($this->orders->loadDetail($order)))->resolve($request);
        $paid = in_array($detail['payment_status'], ['paid', 'partially_refunded'], true);
        $refundOpen = $tickets->hasOpenRefundRequest($order->id);

        return response()->view('account.orders.show', [
            'order' => $detail,
            'canRetry' => $detail['payment_status'] === 'unpaid' && ! $detail['is_final'],
            'canRequestRefund' => $paid && $detail['refund_state'] === 'none' && ! $refundOpen,
            'refundRequested' => $refundOpen,
            'timeline' => self::timeline($detail),
        ])->header('Cache-Control', 'no-store, private');
    }

    /**
     * "Placed", then each status change after it, with its label in the page language.
     *
     * @param  array<string, mixed>  $detail
     * @return list<array{label: string, at: ?string}>
     */
    private static function timeline(array $detail): array
    {
        $events = array_filter($detail['timeline'], static fn (array $event): bool => $event['status'] !== null && $event['status'] !== 'pending_payment');

        return [
            ['label' => __('account.orders.placed_event'), 'at' => $detail['created_at']],
            ...array_map(static fn (array $event): array => [
                'label' => Lang::has('storefront.order.status_value.'.$event['status']) ? __('storefront.order.status_value.'.$event['status']) : Str::headline($event['status']),
                'at' => $event['at'],
            ], array_values($events)),
        ];
    }
}
