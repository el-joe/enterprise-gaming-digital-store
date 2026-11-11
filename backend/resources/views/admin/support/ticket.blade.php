{{--
    Ticket thread (M15, Figma "Admin / Support / Ticket Thread" 41:9540): the conversation with
    internal notes marked "staff only" (never shown to the customer), a reply box with
    "Reply to customer" or "Internal note", status, priority and assignee for support.manage,
    and the customer and linked order on the side.
--}}
@extends('layouts.admin')

@php
    $viewer = auth()->user();
    $api = url('/admin/api/tickets/'.$ticket->public_id);
    $customerName = $ticket->user?->name ?? __('admin.tickets.guest');
@endphp

@section('title', $ticket->ticket_number)
@section('heading', __('admin.tickets.heading', ['number' => $ticket->ticket_number]))

@section('breadcrumbs')
    <x-admin.crumb :href="route('admin.support.tickets.index')">{{ __('admin.tabs.support.tickets') }}</x-admin.crumb>
    <x-admin.crumb>{{ $ticket->ticket_number }}</x-admin.crumb>
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-start justify-between gap-4 md:mb-6">
        <div class="min-w-0">
            <h2 class="font-display text-2xl leading-8 font-bold">{{ $ticket->subject }}</h2>
            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                <span class="font-mono text-accent-text" dir="ltr">{{ $ticket->ticket_number }}</span>
                <x-admin.badge :dot="false">{{ \App\DataTables\Cell::label('ticket_category', $ticket->category?->value) }}</x-admin.badge>
                <x-admin.status group="ticket_priority" :value="$ticket->priority?->value" :dot="false" />
                <x-admin.status group="ticket_status" :value="$ticket->status?->value" />
                <span class="text-content-muted">{{ __('admin.tickets.opened', ['date' => $ticket->created_at?->format('Y-m-d H:i')]) }}</span>
            </div>
        </div>
        @can('support.manage')
            <div class="flex flex-wrap gap-2">
                <form class="flex items-end gap-2" data-module="form" data-action="{{ $api }}" data-method="PATCH" data-success="reload" novalidate>
                    <div>
                        <label for="tk-status" class="field-label">{{ __('admin.col.status') }}</label>
                        <select id="tk-status" name="status" class="admin-input">@foreach (['open', 'pending_staff', 'pending_customer', 'resolved', 'closed'] as $status)<option value="{{ $status }}" @selected($ticket->status?->value === $status)>{{ \App\DataTables\Cell::label('ticket_status', $status) }}</option>@endforeach</select>
                    </div>
                    <div>
                        <label for="tk-priority" class="field-label">{{ __('admin.col.priority') }}</label>
                        <select id="tk-priority" name="priority" class="admin-input">@foreach (['low', 'normal', 'high', 'urgent'] as $priority)<option value="{{ $priority }}" @selected($ticket->priority?->value === $priority)>{{ \App\DataTables\Cell::label('ticket_priority', $priority) }}</option>@endforeach</select>
                    </div>
                    <button type="submit" class="admin-btn">{{ __('admin.common.save') }}</button>
                    <x-admin.form-alerts />
                </form>
                <form class="flex items-end gap-2" data-module="form" data-action="{{ $api.'/assign' }}" data-method="POST" data-success="reload" novalidate>
                    <div>
                        <label for="tk-assignee" class="field-label">{{ __('admin.col.assignee') }}</label>
                        <select id="tk-assignee" name="assigned_to" class="admin-input" data-nullable>
                            <option value="">{{ __('admin.tickets.unassigned') }}</option>
                            @foreach ($assignable as $id => $name)<option value="{{ $id }}" @selected($ticket->assigned_to === $id)>{{ $name }}</option>@endforeach
                        </select>
                        <p class="field-error" data-error-for="assigned_to" hidden></p>
                    </div>
                    <button type="submit" class="admin-btn">{{ __('admin.tickets.assign') }}</button>
                </form>
            </div>
        @endcan
    </div>

    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="flex min-w-0 flex-col gap-3">
            @foreach ($ticket->supportTicketMessages as $message)
                @php
                    $author = $message->author_type?->value;
                    $name = match ($author) { 'staff' => $authors[$message->author_id] ?? __('admin.tickets.staff'), 'customer' => $customerName, default => __('admin.tickets.system') };
                @endphp
                @if ($author === 'system')
                    <p class="flex items-center gap-2 px-2 text-xs text-content-muted"><x-store.icon name="refresh" :size="14" /> {{ $message->body }} · <span dir="ltr">{{ $message->created_at?->format('Y-m-d H:i') }}</span></p>
                @else
                    <article @class([
                        'rounded-lg border p-4',
                        'border-status-warning bg-status-warning-tint' => $message->is_internal,
                        'border-line-subtle bg-surface-raised' => ! $message->is_internal,
                    ])>
                        <header class="mb-2 flex flex-wrap items-center gap-2 text-xs">
                            <span class="text-sm font-semibold text-content-primary">{{ $name }}</span>
                            <span class="text-content-muted">{{ \App\DataTables\Cell::label('actor', $author) }}</span>
                            @if ($message->is_internal)
                                <span class="inline-flex items-center gap-1 font-semibold text-status-warning"><x-store.icon name="lock" :size="12" /> {{ __('admin.tickets.internal_badge') }}</span>
                            @endif
                            <span class="ms-auto text-content-muted" dir="ltr">{{ $message->created_at?->format('Y-m-d H:i') }}</span>
                        </header>
                        <p class="text-sm whitespace-pre-line text-content-primary">{{ $message->body }}</p>
                    </article>
                @endif
            @endforeach

            @can('support.reply')
                <x-admin.card class="border-accent-primary">
                    <form class="flex flex-col gap-3" data-module="form" data-action="{{ $api.'/messages' }}" data-method="POST" data-success="reload" novalidate>
                        <fieldset class="flex flex-wrap gap-4 text-sm">
                            <legend class="sr-only">{{ __('admin.tickets.reply_kind') }}</legend>
                            <label class="flex items-center gap-2"><input type="radio" name="is_internal" value="0" checked class="accent-(--color-accent-text)"> {{ __('admin.tickets.reply_customer') }}</label>
                            <label class="flex items-center gap-2"><input type="radio" name="is_internal" value="1" class="accent-(--color-accent-text)"> {{ __('admin.tickets.internal_note') }}</label>
                        </fieldset>
                        <label for="tk-body" class="sr-only">{{ __('admin.tickets.message') }}</label>
                        <textarea id="tk-body" name="body" rows="4" maxlength="10000" class="admin-input py-2" placeholder="{{ __('admin.tickets.message') }}"></textarea>
                        <p class="field-error" data-error-for="body" hidden></p>
                        <p class="field-help">{{ __('admin.tickets.reply_note') }}</p>
                        <x-admin.form-alerts />
                        <div class="flex justify-end"><button type="submit" class="admin-btn-primary"><x-store.icon name="send" :size="16" /> {{ __('admin.tickets.send') }}</button></div>
                    </form>
                </x-admin.card>
            @endcan
        </div>

        <div class="flex flex-col gap-4 md:gap-6">
            <x-admin.card :title="__('admin.tickets.customer')">
                @if ($ticket->user && $viewer->can('customers.view'))
                    <x-slot:actions><a href="{{ route('admin.customers.show', $ticket->user->public_id ?? '') }}" class="text-sm font-medium text-accent-text">{{ __('admin.common.open') }}</a></x-slot:actions>
                @endif
                <p class="text-sm font-semibold">{{ $customerName }}</p>
                @if ($viewer->can('customers.view_pii'))
                    <p class="text-xs text-content-muted" dir="ltr">{{ $ticket->user?->email ?? $ticket->guest_email }}</p>
                @endif
            </x-admin.card>
            @if ($ticket->order)
                <x-admin.card :title="__('admin.tickets.linked_order')">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        @can('orders.view')
                            <a href="{{ route('admin.orders.show', $ticket->order->public_id) }}" class="font-mono text-xs text-accent-text underline-offset-2 hover:underline">{{ $ticket->order->order_number }}</a>
                        @else
                            <span class="font-mono text-xs">{{ $ticket->order->order_number }}</span>
                        @endcan
                        <x-admin.status group="order_status" :value="$ticket->order->status?->value ?? $ticket->order->status" />
                    </div>
                </x-admin.card>
            @endif
        </div>
    </div>
@endsection
