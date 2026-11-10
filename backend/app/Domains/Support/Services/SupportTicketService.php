<?php

namespace App\Domains\Support\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Automation\Services\AutomationOutboxWriter;
use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\Order;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Support\Enums\SupportTicketCategory;
use App\Domains\Support\Enums\SupportTicketMessageAuthorType;
use App\Domains\Support\Enums\SupportTicketPriority;
use App\Domains\Support\Enums\SupportTicketStatus;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Models\SupportTicketMessage;
use App\Domains\Support\Notifications\SupportTicketReplied;
use App\Domains\Support\Repositories\SupportTicketRepository;
use BackedEnum;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Support tickets for signed-in customers and staff.
 *
 * Customers see only their own tickets (others are 404) and only public messages: internal notes
 * are excluded from the query and again by the customer resource. A customer message moves the
 * ticket to `pending_staff`; a public staff reply moves it to `pending_customer` and emails a
 * link (no body); an internal note changes nothing the customer can see. A closed ticket cannot
 * be replied to by the customer. Assignment and status changes are audited.
 */
class SupportTicketService
{
    public function __construct(private SupportTicketRepository $tickets, private AuditLogger $audit, private AutomationOutboxWriter $outbox) {}

    /**
     * @param  array{category: string, subject: string, body: string, order_public_id?: ?string}  $input
     */
    public function open(User $customer, array $input): SupportTicket
    {
        $orderId = null;

        if (($input['order_public_id'] ?? null) !== null) {
            $orderId = Order::query()->where('public_id', $input['order_public_id'])->where('user_id', $customer->id)->value('id')
                ?? throw new ApplicationException('This order was not found.', 'validation_failed', 422, ['order_public_id' => ['This order was not found.']]);
        }

        $ticket = DB::transaction(function () use ($customer, $input, $orderId): SupportTicket {
            $ticket = (new SupportTicket)->forceFill([
                'ticket_number' => 'T-'.now()->format('ymd').'-'.strtoupper(substr((string) Str::ulid(), -8)),
                'user_id' => $customer->id,
                'order_id' => $orderId,
                'category' => SupportTicketCategory::from($input['category']),
                'subject' => $input['subject'],
                'status' => SupportTicketStatus::Open,
                'priority' => SupportTicketPriority::Normal,
                'last_activity_at' => now(),
            ]);
            $ticket->save();

            $this->message($ticket, SupportTicketMessageAuthorType::Customer, $customer->id, $input['body'], false);

            return $ticket;
        });

        return $this->forCustomer($customer, $ticket->public_id);
    }

    public function forCustomer(User $customer, string $publicId): SupportTicket
    {
        return $this->tickets->findForCustomer($customer->id, $publicId) ?? throw (new ModelNotFoundException)->setModel(SupportTicket::class);
    }

    public function customerReply(User $customer, string $publicId, string $body): SupportTicket
    {
        $ticket = $this->forCustomer($customer, $publicId);

        DB::transaction(function () use ($ticket, $customer, $body): void {
            $locked = $this->tickets->lock($ticket->id);

            if ($locked->status === SupportTicketStatus::Closed) {
                throw new ApplicationException('This ticket is closed. Please open a new one.', 'ticket_closed', 409);
            }

            $this->message($locked, SupportTicketMessageAuthorType::Customer, $customer->id, $body, false);
            $locked->forceFill(['status' => SupportTicketStatus::PendingStaff, 'last_activity_at' => now()])->save();
        });

        return $this->forCustomer($customer, $publicId);
    }

    public function staffReply(User $staff, SupportTicket $ticket, string $body, bool $internal): SupportTicket
    {
        DB::transaction(function () use ($staff, $ticket, $body, $internal): void {
            $locked = $this->tickets->lock($ticket->id);
            $this->message($locked, SupportTicketMessageAuthorType::Staff, $staff->id, $body, $internal);

            if ($internal) {
                return;
            }

            $locked->forceFill(['status' => SupportTicketStatus::PendingCustomer, 'last_activity_at' => now()])->save();
            $this->outbox->recordTicketUpdate($locked, 'staff_reply');

            if ($locked->user_id !== null) {
                DB::afterCommit(static function () use ($locked): void {
                    User::query()->find($locked->user_id)?->notify(new SupportTicketReplied($locked->id));
                });
            }
        });

        return $this->tickets->forStaff($ticket->refresh());
    }

    public function assign(User $staff, SupportTicket $ticket, ?int $assigneeId): SupportTicket
    {
        if ($assigneeId !== null) {
            $assignee = User::query()->find($assigneeId);

            if ($assignee === null || ! $assignee->isStaff() || ! $assignee->can(PermissionName::SupportReply->value)) {
                throw new ApplicationException('Tickets can only be assigned to staff who can reply.', 'validation_failed', 422, ['assigned_to' => ['Tickets can only be assigned to staff who can reply.']]);
            }
        }

        $this->change($ticket, ['assigned_to' => $assigneeId], 'support.ticket.assigned');

        return $this->tickets->forStaff($ticket->refresh());
    }

    /**
     * @param  array{status?: string, priority?: string}  $changes
     */
    public function update(SupportTicket $ticket, array $changes): SupportTicket
    {
        $values = [];

        if (isset($changes['status'])) {
            $values['status'] = SupportTicketStatus::from($changes['status']);
        }

        if (isset($changes['priority'])) {
            $values['priority'] = SupportTicketPriority::from($changes['priority']);
        }

        $this->change($ticket, $values, 'support.ticket.updated');

        return $this->tickets->forStaff($ticket->refresh());
    }

    public function forStaff(SupportTicket $ticket): SupportTicket
    {
        return $this->tickets->forStaff($ticket);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function change(SupportTicket $ticket, array $values, string $action): void
    {
        if ($values === []) {
            return;
        }

        DB::transaction(function () use ($ticket, $values, $action): void {
            $locked = $this->tickets->lock($ticket->id);
            $before = [];

            foreach (array_keys($values) as $key) {
                $current = $locked->getAttribute($key);
                $before[$key] = $current instanceof BackedEnum ? $current->value : $current;
            }

            $locked->forceFill([...$values, 'last_activity_at' => now()])->save();

            if (isset($values['status']) && $before['status'] !== $locked->status->value) {
                $this->outbox->recordTicketUpdate($locked, 'status_changed');
            }

            $this->audit->record(new AuditEntry(
                action: $action,
                subject: $locked,
                before: $before,
                after: array_map(static fn (mixed $value): mixed => $value instanceof BackedEnum ? $value->value : $value, $values),
            ));
        });
    }

    private function message(SupportTicket $ticket, SupportTicketMessageAuthorType $author, ?int $authorId, string $body, bool $internal): SupportTicketMessage
    {
        $message = (new SupportTicketMessage)->forceFill([
            'support_ticket_id' => $ticket->id,
            'author_type' => $author,
            'author_id' => $authorId,
            'body' => trim(strip_tags($body)),
            'is_internal' => $internal,
        ]);
        $message->save();

        return $message;
    }
}
