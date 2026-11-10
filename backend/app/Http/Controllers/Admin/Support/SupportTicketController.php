<?php

namespace App\Http\Controllers\Admin\Support;

use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Repositories\SupportTicketRepository;
use App\Domains\Support\Services\SupportTicketService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Support\AssignTicketRequest;
use App\Http\Requests\Admin\Support\StaffTicketReplyRequest;
use App\Http\Requests\Admin\Support\UpdateTicketRequest;
use App\Http\Resources\Admin\Support\SupportTicketResource;
use Illuminate\Http\JsonResponse;

class SupportTicketController extends Controller
{
    public function __construct(private SupportTicketService $service) {}

    public function index(AdminListRequest $request, SupportTicketRepository $tickets): JsonResponse
    {
        return $this->paginated($tickets->staffPage($request->toListQuery()), SupportTicketResource::class);
    }

    public function show(SupportTicket $ticket): JsonResponse
    {
        return $this->success(SupportTicketResource::make($this->service->forStaff($ticket))->resolve());
    }

    public function reply(StaffTicketReplyRequest $request, SupportTicket $ticket): JsonResponse
    {
        $internal = $request->boolean('is_internal');

        return $this->success(
            SupportTicketResource::make($this->service->staffReply($request->user(), $ticket, $request->validated('body'), $internal))->resolve(),
            $internal ? 'Internal note added.' : 'Reply sent.',
            status: 201,
        );
    }

    public function assign(AssignTicketRequest $request, SupportTicket $ticket): JsonResponse
    {
        $assignee = $request->validated('assigned_to');

        return $this->success(SupportTicketResource::make($this->service->assign($request->user(), $ticket, $assignee === null ? null : (int) $assignee))->resolve(), 'Assigned.');
    }

    public function update(UpdateTicketRequest $request, SupportTicket $ticket): JsonResponse
    {
        return $this->success(SupportTicketResource::make($this->service->update($ticket, $request->validated()))->resolve(), 'Saved.');
    }
}
