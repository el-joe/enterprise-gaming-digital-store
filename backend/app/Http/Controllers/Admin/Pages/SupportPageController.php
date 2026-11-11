<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Support\Models\RefundRequest;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Services\RefundRequestService;
use App\Domains\Support\Services\SupportTicketService;
use App\Http\Controllers\Controller;
use App\Http\Support\AdminTablePage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Support (M15, spec Phase 9.10, Figma "Admin / Support / Tickets" and "Ticket Thread"): the
 * ticket queue, the thread with replies and internal notes (staff only, never shown to the
 * customer), assignment, status and priority; refund requests; and review moderation.
 */
class SupportPageController extends Controller
{
    public function tickets(Request $request, AdminTablePage $tables): View
    {
        return view('admin.support.tickets', ['table' => $tables->make('tickets', $request->user())]);
    }

    public function ticket(Request $request, SupportTicket $ticket, SupportTicketService $tickets): Response
    {
        $ticket = $tickets->forStaff($ticket);

        return response()->view('admin.support.ticket', [
            'ticket' => $ticket,
            'authors' => $tickets->staffAuthors($ticket),
            'assignable' => $request->user()->can('support.manage') ? $tickets->assignableStaff() : [],
        ])->header('Cache-Control', 'no-store, private');
    }

    public function refundRequests(Request $request, AdminTablePage $tables): View
    {
        return view('admin.support.refund-requests', ['table' => $tables->make('refund-requests', $request->user())]);
    }

    public function refundRequest(RefundRequest $refundRequest, RefundRequestService $requests): Response
    {
        return response()->view('admin.support.refund-request', [
            'refundRequest' => $requests->detailed($refundRequest),
            'units' => $requests->orderUnits($refundRequest),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function reviews(Request $request, AdminTablePage $tables): View
    {
        return view('admin.support.reviews', ['table' => $tables->make('reviews', $request->user())]);
    }
}
