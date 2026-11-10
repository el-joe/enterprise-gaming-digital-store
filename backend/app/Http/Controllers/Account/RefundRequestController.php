<?php

namespace App\Http\Controllers\Account;

use App\Domains\Support\Repositories\SupportTicketRepository;
use App\Domains\Support\Services\RefundRequestService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\PageRequest;
use App\Http\Requests\Account\StoreRefundRequestRequest;
use App\Http\Resources\Account\RefundRequestResource;
use Illuminate\Http\JsonResponse;

class RefundRequestController extends Controller
{
    public function __construct(private RefundRequestService $service, private SupportTicketRepository $tickets) {}

    public function index(PageRequest $request): JsonResponse
    {
        return $this->paginated($this->tickets->refundRequestsForCustomer($request->user()->id, $request->page(), $request->perPage()), RefundRequestResource::class);
    }

    public function store(StoreRefundRequestRequest $request): JsonResponse
    {
        $refundRequest = $this->service->request(
            $request->user(),
            $request->validated('order_public_id'),
            $request->validated('reason'),
            $request->validated('support_ticket_public_id'),
        );

        return $this->success(['refund_request' => RefundRequestResource::make($refundRequest)->resolve()], 'Refund request received.', status: 201);
    }
}
