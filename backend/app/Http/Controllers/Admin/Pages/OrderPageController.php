<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Services\AdminOrderReader;
use App\Domains\Payments\Enums\RefundReason;
use App\Http\Controllers\Controller;
use App\Http\Support\AdminTablePage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Orders list and detail (M14, spec Phase 9.4). Actions on the detail page call the JSON
 * routes (`/admin/api/orders/{order}/...`), each behind its own permission.
 */
class OrderPageController extends Controller
{
    public function index(Request $request, AdminTablePage $tables): View
    {
        return view('admin.orders.index', ['table' => $tables->make('orders', $request->user())]);
    }

    public function show(Request $request, Order $order, AdminOrderReader $reader): Response
    {
        return response()->view('admin.orders.show', [
            'order' => $reader->detail($order, $request->user()),
            'refundReasons' => array_values(array_filter(RefundReason::cases(), static fn (RefundReason $reason): bool => $reason !== RefundReason::Duplicate)),
        ])->header('Cache-Control', 'no-store, private');
    }
}
