<?php

namespace App\Http\Controllers\Web\Account;

use App\Domains\Orders\Repositories\CustomerOrderRepository;
use App\Domains\Support\Enums\SupportTicketCategory;
use App\Domains\Support\Repositories\SupportTicketRepository;
use App\Domains\Support\Services\SupportTicketService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Account\AccountPageRequest;
use App\Http\Resources\Account\SupportTicketResource;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Support tickets (M13, Figma "Account Tickets"): the list, a new-ticket form (optionally about
 * one of the customer's orders) and a ticket thread with replies. `/account/tickets/{public_id}`
 * is the page the reply emails link to. Another customer's ticket is 404 (the service looks it up
 * by owner); internal staff notes are never included.
 */
class TicketController extends Controller
{
    public function __construct(private SupportTicketRepository $tickets) {}

    public function index(AccountPageRequest $request): View
    {
        return view('account.tickets.index', $this->listData($request) + ['ticket' => null]);
    }

    public function create(AccountPageRequest $request, CustomerOrderRepository $orders): View
    {
        $orderPublicId = $request->query('order');
        $order = is_string($orderPublicId) ? $orders->findForCustomer($request->user()->id, $orderPublicId) : null;

        return view('account.tickets.create', [
            'categories' => array_map(static fn (SupportTicketCategory $category): string => $category->value, SupportTicketCategory::cases()),
            'order' => $order === null ? null : ['public_id' => $order->public_id, 'number' => $order->order_number],
        ]);
    }

    public function show(AccountPageRequest $request, string $ticketPublicId, SupportTicketService $service): View
    {
        $ticket = (new SupportTicketResource($service->forCustomer($request->user(), $ticketPublicId)))->resolve($request);

        return view('account.tickets.index', $this->listData($request) + ['ticket' => $ticket]);
    }

    /**
     * @return array<string, mixed>
     */
    private function listData(Request $request): array
    {
        $page = $this->tickets->forCustomer($request->user()->id, max(1, (int) $request->query('page', 1)), AccountPageRequest::PER_PAGE)
            ->withPath(url()->localized('storefront.account.tickets'));

        return [
            'tickets' => SupportTicketResource::collection($page->getCollection())->resolve($request),
            'paginator' => $page,
        ];
    }
}
