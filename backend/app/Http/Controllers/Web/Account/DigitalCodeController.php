<?php

namespace App\Http\Controllers\Web\Account;

use App\Domains\Fulfillment\Services\CodeRevealService;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Repositories\CustomerOrderRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Account\AccountPageRequest;
use App\Http\Requests\Web\Account\CodeListPageRequest;
use Illuminate\Http\Response;

/**
 * Digital code history (`/account/codes`, M13, Figma RTL "Account / Digital Codes"): every code
 * the customer received, masked, with the shared reveal component. Codes never reach the page
 * before a reveal; each reveal goes through `POST /api/v1/account/orders/{order}/units/{unit}/reveal`
 * (recent password, audited, `no-store`).
 */
class DigitalCodeController extends Controller
{
    public function index(CodeListPageRequest $request, CustomerOrderRepository $orders): Response
    {
        $page = $orders->paginateCodeUnits($request->user()->id, $request->state(), $request->search(), $request->page(), AccountPageRequest::PER_PAGE)
            ->withPath($request->url())
            ->appends(array_filter(['state' => $request->state(), 'q' => $request->search()]));

        $units = $page->getCollection()->map(static fn (OrderItemUnit $unit): array => [
            'public_id' => $unit->public_id,
            'status' => $unit->status->value,
            'name' => $unit->orderItem?->name_snapshot,
            'product_slug' => $unit->orderItem?->product?->slug,
            'order_public_id' => $unit->order?->public_id,
            'order_number' => $unit->order?->order_number,
            'masked_code' => $unit->digitalCode?->masked_code,
            'expires_at' => $unit->digitalCode?->expires_at?->toIso8601String(),
            'delivered_at' => $unit->delivered_at?->toIso8601String(),
            'first_revealed_at' => $unit->first_revealed_at?->toIso8601String(),
            'refunded' => in_array($unit->status, [OrderItemUnitStatus::Refunded, OrderItemUnitStatus::RefundPending], true),
            'revealable' => $unit->order !== null && CodeRevealService::isRevealable($unit->order, $unit, $unit->digitalCode !== null),
        ])->all();

        return response()->view('account.codes', [
            'units' => $units,
            'paginator' => $page,
            'state' => $request->state(),
            'search' => $request->search(),
        ])->header('Cache-Control', 'no-store, private');
    }
}
