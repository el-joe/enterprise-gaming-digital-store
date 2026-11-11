<?php

namespace App\DataTables\Support;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Support\Enums\SupportTicketCategory;
use App\Domains\Support\Enums\SupportTicketPriority;
use App\Domains\Support\Enums\SupportTicketStatus;
use App\Domains\Support\Models\SupportTicket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Support tickets (spec Phase 9.10, Figma "Admin / Support / Tickets"): the agent queue by status, priority and assignee.
 */
class TicketsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'tickets';
    }

    public static function permission(): string
    {
        return 'support.view';
    }

    public function columns(): array
    {
        return [
            new Column('ticket', __('admin.col.ticket'), 'support_tickets.ticket_number', html: true),
            new Column('subject', __('admin.col.subject'), 'support_tickets.subject', html: true),
            new Column('customer', __('admin.col.customer'), html: true),
            new Column('category', __('admin.col.category'), 'support_tickets.category'),
            new Column('status', __('admin.col.status'), 'support_tickets.status', html: true),
            new Column('priority', __('admin.col.priority'), 'support_tickets.priority', html: true),
            new Column('assignee', __('admin.col.assignee'), html: true),
            new Column('activity', __('admin.col.activity'), 'support_tickets.last_activity_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['activity', 'desc'];
    }

    protected function searchColumns(): array
    {
        return ['support_tickets.ticket_number', 'support_tickets.subject'];
    }

    public function filters(): array
    {
        $viewerId = $this->viewer->id;

        return [
            new Filter('status', __('admin.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('support_tickets.status', $value);
            }, $this->enumOptions('ticket_status', SupportTicketStatus::cases())),
            new Filter('priority', __('admin.col.priority'), 'select', static function (Builder $query, string $value): void {
                $query->where('support_tickets.priority', $value);
            }, $this->enumOptions('ticket_priority', SupportTicketPriority::cases())),
            new Filter('category', __('admin.col.category'), 'select', static function (Builder $query, string $value): void {
                $query->where('support_tickets.category', $value);
            }, $this->enumOptions('ticket_category', SupportTicketCategory::cases())),
            new Filter('assignee', __('admin.col.assignee'), 'select', static function (Builder $query, string $value) use ($viewerId): void {
                $value === 'me' ? $query->where('support_tickets.assigned_to', $viewerId) : $query->whereNull('support_tickets.assigned_to');
            }, ['me' => __('admin.tickets.mine'), 'none' => __('admin.tickets.unassigned')]),
        ];
    }

    public function rowId(Model $row): string
    {
        return (string) $row->getAttribute('public_id');
    }

    protected function idColumn(): string
    {
        return 'support_tickets.public_id';
    }

    protected function query(): Builder
    {
        return SupportTicket::query()->select('support_tickets.*')->with(['user:id,name', 'assignedTo:id,name']);
    }

    /**
     * @param  SupportTicket  $row
     */
    protected function cells(Model $row): array
    {

        return [
            'ticket' => Cell::link(route('admin.support.tickets.show', $row->public_id), (string) $row->ticket_number, true),
            'subject' => Str::limit((string) $row->subject, 80),
            'customer' => e((string) ($row->user?->name ?? __('admin.tickets.guest'))),
            'category' => Cell::label('ticket_category', $row->category?->value),
            'status' => Cell::status('ticket_status', $row->status?->value),
            'priority' => Cell::status('ticket_priority', $row->priority?->value),
            'assignee' => $row->assignedTo === null ? Cell::muted(__('admin.tickets.unassigned')) : e((string) $row->assignedTo->name),
            'activity' => $this->dateTime($row->last_activity_at),
        ];
    }
}
