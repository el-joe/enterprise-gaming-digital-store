<?php

namespace App\Http\Controllers\Admin\Support;

use App\Domains\Payments\Enums\RefundDestination;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Support\Models\RefundRequest;
use App\Domains\Support\Repositories\SupportTicketRepository;
use App\Domains\Support\Services\RefundRequestService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Support\DecideRefundRequestRequest;
use App\Http\Resources\Admin\Support\RefundRequestResource;
use Illuminate\Http\JsonResponse;

class RefundRequestController extends Controller
{
    public function __construct(private RefundRequestService $service) {}

    public function index(AdminListRequest $request, SupportTicketRepository $tickets): JsonResponse
    {
        return $this->paginated($tickets->refundRequestsPage($request->toListQuery()), RefundRequestResource::class);
    }

    public function approve(DecideRefundRequestRequest $request, RefundRequest $refundRequest): JsonResponse
    {
        $decided = $this->service->approve(
            $refundRequest,
            $request->user(),
            $request->validated('unit_ids'),
            RefundReason::from($request->validated('reason')),
            $request->validated('note'),
            RefundDestination::from($request->validated('destination', RefundDestination::OriginalMethod->value)),
        );

        return $this->success(RefundRequestResource::make($decided)->resolve(), 'Refund requested.');
    }

    public function reject(DecideRefundRequestRequest $request, RefundRequest $refundRequest): JsonResponse
    {
        return $this->success(RefundRequestResource::make($this->service->reject($refundRequest, $request->user(), $request->validated('note')))->resolve(), 'Rejected.');
    }
}
