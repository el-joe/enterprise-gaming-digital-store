<?php

namespace App\Http\Controllers\Account;

use App\Domains\Support\Repositories\SupportTicketRepository;
use App\Domains\Support\Services\SupportTicketService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\PageRequest;
use App\Http\Requests\Account\StoreTicketRequest;
use App\Http\Requests\Account\TicketReplyRequest;
use App\Http\Resources\Account\SupportTicketResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function __construct(private SupportTicketService $service, private SupportTicketRepository $tickets) {}

    public function index(PageRequest $request): JsonResponse
    {
        return $this->paginated($this->tickets->forCustomer($request->user()->id, $request->page(), $request->perPage()), SupportTicketResource::class);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        return $this->success(['ticket' => SupportTicketResource::make($this->service->open($request->user(), $request->validated()))->resolve()], 'Ticket opened.', status: 201);
    }

    public function show(Request $request, string $ticket): JsonResponse
    {
        return $this->success(['ticket' => SupportTicketResource::make($this->service->forCustomer($request->user(), $ticket))->resolve()]);
    }

    public function reply(TicketReplyRequest $request, string $ticket): JsonResponse
    {
        return $this->success(['ticket' => SupportTicketResource::make($this->service->customerReply($request->user(), $ticket, $request->validated('body')))->resolve()], 'Message sent.', status: 201);
    }
}
