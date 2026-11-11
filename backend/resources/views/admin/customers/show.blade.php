{{--
    Customer detail (M15, Figma "Admin / Customers / Detail" 31:5980): header with status and
    actions, KPIs, recent orders, wallet ledger, notes, tickets and audit trail; profile, risk,
    wallet adjustment, devices with biometric keys (OD-1) and privacy on the side. Every action
    is a JSON route with its own permission, confirmed and audited with a reason.
--}}
@extends('layouts.admin')

@php
    $viewer = auth()->user();
    $manage = $viewer->can('customers.manage');
    $adjust = $viewer->can('customers.wallet_adjust');
    $api = url('/admin/api/customers/'.$customer->public_id);
    $blocked = $customer->status?->value === 'suspended';
    $money = app(\App\Domains\Currency\Services\CurrencyConverter::class);
    $present = fn ($amount, $currency) => $money->present(\App\Domains\Shared\ValueObjects\Money::of((string) $amount, (string) $currency))['formatted'];
@endphp

@section('title', $customer->name)
@section('heading', __('admin.customers.title'))

@section('breadcrumbs')
    <x-admin.crumb :href="route('admin.customers.index')">{{ __('admin.nav.customers') }}</x-admin.crumb>
    <x-admin.crumb>{{ $customer->name }}</x-admin.crumb>
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-start justify-between gap-4 md:mb-6">
        <div class="flex min-w-0 items-center gap-4">
            <span class="flex size-14 shrink-0 items-center justify-center rounded-md bg-status-info-tint font-semibold text-accent-ultra">{{ mb_strtoupper(mb_substr((string) $customer->name, 0, 2)) }}</span>
            <div class="min-w-0">
                <h2 class="truncate font-display text-2xl leading-8 font-bold">{{ $customer->name }}</h2>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    <x-admin.status group="user_status" :value="$customer->status?->value" :dot="false" />
                    <x-admin.badge :tone="\App\DataTables\Cell::tone('risk_level', $profile?->risk_level?->value ?? 'low')" :dot="false">{{ __('admin.customers.risk_badge', ['level' => \App\DataTables\Cell::label('risk_level', $profile?->risk_level?->value ?? 'low')]) }}</x-admin.badge>
                    <x-admin.badge :tone="$customer->email_verified_at ? 'info' : 'warning'" :dot="false">{{ $customer->email_verified_at ? __('admin.customers.email_verified') : __('admin.customers.email_unverified') }}</x-admin.badge>
                </div>
                <p class="mt-1 text-xs text-content-muted">{{ __('admin.customers.since', ['date' => $customer->created_at?->format('Y-m-d')]) }} · <span class="font-mono" dir="ltr">{{ $customer->public_id }}</span></p>
            </div>
        </div>
        @if ($manage)
            <div class="flex flex-wrap gap-2" data-module="action">
                @if ($blocked)
                    <button type="button" class="admin-btn" data-action-url="{{ $api.'/unblock' }}" data-confirm-title="{{ __('admin.customers.unblock_confirm') }}" data-reason="{{ __('admin.common.reason') }}">{{ __('admin.customers.unblock') }}</button>
                @elseif ($customer->status?->value === 'active')
                    <button type="button" class="admin-btn-danger" data-action-url="{{ $api.'/block' }}" data-confirm-title="{{ __('admin.customers.block_confirm') }}" data-confirm-text="{{ __('admin.customers.block_text') }}" data-confirm-danger data-reason="{{ __('admin.common.reason') }}"><x-store.icon name="lock" :size="16" /> {{ __('admin.customers.block') }}</button>
                @endif
            </div>
        @endif
    </div>

    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="flex min-w-0 flex-col gap-4 md:gap-6">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <x-admin.kpi :label="__('admin.customers.lifetime')" :value="$present($profile?->lifetime_value_base ?? '0', $wallet->currency)" :note="__('admin.customers.base_note')" />
                <x-admin.kpi :label="__('admin.customers.orders')" :value="(string) $ordersCount" :note="trans_choice('admin.customers.reviews', $reviewsCount, ['count' => $reviewsCount])" />
                <x-admin.kpi :label="__('admin.customers.wallet_balance')" :value="$present($wallet->balance, $wallet->currency)" :note="\App\DataTables\Cell::label('wallet_status', $wallet->status?->value)" />
                <x-admin.kpi :label="__('admin.customers.last_order')" :value="$profile?->last_order_at?->format('Y-m-d') ?? '—'" :note="__('admin.customers.first_order', ['date' => $profile?->first_order_at?->format('Y-m-d') ?? '—'])" />
            </div>

            <x-admin.card :title="__('admin.customers.recent_orders')" :padded="false">
                @can('orders.view')
                    <x-slot:actions><a href="{{ route('admin.orders.index', ['q' => $customer->name]) }}" class="text-sm font-medium text-accent-text">{{ __('admin.common.view_all') }}</a></x-slot:actions>
                @endcan
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr><th class="admin-th">{{ __('admin.col.order') }}</th><th class="admin-th">{{ __('admin.customers.placed') }}</th><th class="admin-th text-end">{{ __('admin.customers.total') }}</th><th class="admin-th">{{ __('admin.col.status') }}</th></tr></thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="admin-td font-mono text-xs">@can('orders.view')<a href="{{ route('admin.orders.show', $order->public_id) }}" class="underline-offset-2 hover:underline">{{ $order->order_number }}</a>@else{{ $order->order_number }}@endcan</td>
                                    <td class="admin-td" dir="ltr">{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="admin-td text-end font-semibold" dir="ltr">{{ $present($order->display_total, $order->display_currency) }}</td>
                                    <td class="admin-td"><x-admin.status group="order_status" :value="$order->status?->value ?? $order->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="admin-td text-center text-content-muted">{{ __('admin.customers.no_orders') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>

            <x-admin.card :title="__('admin.customers.ledger')" :subtitle="__('admin.customers.balance', ['amount' => $present($wallet->balance, $wallet->currency)])" :padded="false">
                <x-admin.table :table="$ledger" :title="__('admin.customers.ledger')" class="border-0 shadow-none" />
            </x-admin.card>

            <x-admin.card :title="__('admin.customers.tickets')" :padded="false">
                <ul class="divide-y divide-line-subtle">
                    @forelse ($tickets as $ticket)
                        <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                            <span class="min-w-0">
                                @can('support.view')<a href="{{ route('admin.support.tickets.show', $ticket->public_id) }}" class="font-mono text-xs underline-offset-2 hover:underline">{{ $ticket->ticket_number }}</a>@else<span class="font-mono text-xs">{{ $ticket->ticket_number }}</span>@endcan
                                <span class="block truncate">{{ $ticket->subject }}</span>
                            </span>
                            <x-admin.status group="ticket_status" :value="$ticket->status?->value" />
                        </li>
                    @empty
                        <li class="px-5 py-4 text-sm text-content-muted">{{ __('admin.customers.no_tickets') }}</li>
                    @endforelse
                </ul>
            </x-admin.card>

            <x-admin.card :title="__('admin.customers.notes')" :subtitle="__('admin.customers.notes_note')">
                @if ($manage)
                    <form class="mb-4 flex flex-col gap-2" data-module="form" data-action="{{ $api.'/notes' }}" data-method="POST" data-success="reload" novalidate>
                        <label for="cn-body" class="sr-only">{{ __('admin.customers.add_note') }}</label>
                        <textarea id="cn-body" name="body" rows="2" maxlength="5000" class="admin-input py-2" placeholder="{{ __('admin.customers.add_note') }}"></textarea>
                        <p class="field-error" data-error-for="body" hidden></p>
                        <x-admin.form-alerts />
                        <div><button type="submit" class="admin-btn">{{ __('admin.customers.save_note') }}</button></div>
                    </form>
                @endif
                <ul class="flex flex-col gap-2">
                    @forelse ($notes as $note)
                        <li class="rounded-md bg-surface-sunken px-3 py-2 text-sm">
                            <p class="text-xs text-content-muted">{{ $note->author?->name ?? '—' }} · <span dir="ltr">{{ $note->created_at?->format('Y-m-d H:i') }}</span></p>
                            <p class="mt-1 whitespace-pre-line">{{ $note->body }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-content-muted">{{ __('admin.customers.no_notes') }}</li>
                    @endforelse
                </ul>
            </x-admin.card>

            @if ($audit !== null)
                <x-admin.card :title="__('admin.customers.audit')">
                    <ul class="flex flex-col divide-y divide-line-subtle">
                        @forelse ($audit as $entry)
                            <li class="grid grid-cols-[110px_1fr] gap-3 py-2 text-sm">
                                <span class="font-mono text-xs text-content-muted" dir="ltr">{{ $entry->created_at?->format('Y-m-d H:i') }}</span>
                                <span><span class="font-mono text-xs">{{ $entry->action }}</span><span class="block text-xs text-content-muted">{{ \App\DataTables\Cell::label('actor', $entry->actor_type?->value) }}@if ($entry->reason) · {{ $entry->reason }}@endif</span></span>
                            </li>
                        @empty
                            <li class="py-2 text-sm text-content-muted">{{ __('admin.common.none') }}</li>
                        @endforelse
                    </ul>
                </x-admin.card>
            @endif
        </div>

        <div class="flex flex-col gap-4 md:gap-6">
            <x-admin.card :title="__('admin.customers.profile')">
                <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                    <dt class="text-content-muted">{{ __('admin.customers.email') }}</dt><dd class="font-mono text-xs break-all" dir="ltr">{{ $contact['email'] }}</dd>
                    <dt class="text-content-muted">{{ __('admin.customers.phone') }}</dt><dd class="font-mono text-xs" dir="ltr">{{ $contact['phone'] ?? '—' }}</dd>
                    <dt class="text-content-muted">{{ __('admin.col.country') }}</dt><dd>{{ $customer->country_code ?? '—' }}</dd>
                    <dt class="text-content-muted">{{ __('admin.customers.locale') }}</dt><dd>{{ $customer->locale ?? '—' }}</dd>
                    <dt class="text-content-muted">{{ __('admin.customers.display_currency') }}</dt><dd>{{ $customer->display_currency ?? '—' }}</dd>
                    <dt class="text-content-muted">{{ __('admin.customers.last_login') }}</dt><dd dir="ltr">{{ $customer->last_login_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                </dl>
                @cannot('customers.view_pii')<p class="mt-3 text-xs text-content-muted">{{ __('admin.customers.pii_masked') }}</p>@endcannot
            </x-admin.card>

            <x-admin.card :title="__('admin.customers.risk')">
                <p class="text-sm text-content-secondary">{{ $profile?->risk_notes ?? __('admin.customers.no_risk_notes') }}</p>
                @if ($manage)
                    <form class="mt-3 flex flex-col gap-3" data-module="form" data-action="{{ $api.'/risk' }}" data-method="PUT" data-success="reload" novalidate>
                        <div>
                            <label for="cr-level" class="field-label">{{ __('admin.customers.risk_level') }}</label>
                            <select id="cr-level" name="risk_level" class="admin-input">
                                @foreach (['low', 'medium', 'high', 'blocked'] as $level)<option value="{{ $level }}" @selected(($profile?->risk_level?->value ?? 'low') === $level)>{{ \App\DataTables\Cell::label('risk_level', $level) }}</option>@endforeach
                            </select>
                            <p class="field-error" data-error-for="risk_level" hidden></p>
                        </div>
                        <div>
                            <label for="cr-notes" class="field-label">{{ __('admin.customers.risk_notes') }}</label>
                            <textarea id="cr-notes" name="risk_notes" rows="2" maxlength="2000" class="admin-input py-2" data-nullable>{{ $profile?->risk_notes }}</textarea>
                        </div>
                        <x-admin.field name="reason" id="cr-reason" :label="__('admin.common.reason')" maxlength="500" :help="__('admin.common.audited')" />
                        <x-admin.form-alerts />
                        <div><button type="submit" class="admin-btn">{{ __('admin.customers.save_risk') }}</button></div>
                    </form>
                @endif
            </x-admin.card>

            @if ($adjust)
                <x-admin.card :title="__('admin.customers.adjust')" :subtitle="__('admin.customers.adjust_note', ['currency' => $wallet->currency])">
                    <form class="flex flex-col gap-3" data-module="form" data-action="{{ $api.'/wallet/adjustments' }}" data-method="POST" data-success="reload" data-confirm="{{ __('admin.customers.adjust_confirm') }}" novalidate>
                        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
                        <div>
                            <label for="wa-direction" class="field-label">{{ __('admin.customers.direction') }}</label>
                            <select id="wa-direction" name="direction" class="admin-input"><option value="credit">{{ __('admin.customers.credit') }}</option><option value="debit">{{ __('admin.customers.debit') }}</option></select>
                            <p class="field-error" data-error-for="direction" hidden></p>
                        </div>
                        <x-admin.field name="amount" id="wa-amount" :label="__('admin.customers.amount', ['currency' => $wallet->currency])" inputmode="decimal" dir="ltr" />
                        <x-admin.field name="reason" id="wa-reason" :label="__('admin.customers.adjust_reason')" maxlength="255" :help="__('admin.customers.adjust_reason_help')" />
                        <x-admin.form-alerts />
                        <div><button type="submit" class="admin-btn-primary"><x-store.icon name="check" :size="16" /> {{ __('admin.customers.apply') }}</button></div>
                    </form>
                </x-admin.card>
            @endif

            <x-admin.card :title="__('admin.customers.devices')" :subtitle="__('admin.customers.devices_note')">
                <ul class="flex flex-col gap-2 text-sm" data-module="action">
                    @forelse ($devices as $device)
                        <li class="flex items-start justify-between gap-3">
                            <span class="min-w-0">
                                <span class="block font-medium">{{ $device->type->value === 'mobile' ? ($device->deviceName ?? __('admin.customers.app')) : \Illuminate\Support\Str::limit((string) $device->userAgent, 60) }}</span>
                                <span class="block text-xs text-content-muted" dir="ltr">{{ \App\Http\Resources\Account\DeviceResource::maskIp($device->ip) ?? '—' }} · {{ $device->lastUsedAt?->format('Y-m-d H:i') ?? '—' }}</span>
                            </span>
                            @if ($manage)
                                <button type="button" class="admin-btn h-8 shrink-0 text-status-danger" data-action-url="{{ $api.'/devices/'.$device->id }}" data-action-method="DELETE" data-confirm-title="{{ __('admin.customers.revoke_device') }}" data-confirm-danger data-reason="{{ __('admin.common.reason') }}">{{ __('admin.common.revoke') }}</button>
                            @endif
                        </li>
                    @empty
                        <li class="text-content-muted">{{ __('admin.customers.no_devices') }}</li>
                    @endforelse
                </ul>
                <h3 class="mt-4 border-t border-line-subtle pt-3 text-sm font-semibold">{{ __('admin.customers.biometric_keys') }}</h3>
                <ul class="mt-2 flex flex-col gap-2 text-sm" data-module="action">
                    @forelse ($biometricKeys as $key)
                        <li class="flex items-start justify-between gap-3">
                            <span class="min-w-0"><span class="block font-mono text-xs" dir="ltr">{{ \Illuminate\Support\Str::limit((string) $key->key_fingerprint, 16, '…') }}</span><span class="block text-xs text-content-muted" dir="ltr">{{ __('admin.customers.key_added', ['date' => $key->created_at?->format('Y-m-d')]) }} · {{ $key->last_used_at?->format('Y-m-d H:i') ?? __('admin.common.never') }}</span></span>
                            @if ($manage)
                                <button type="button" class="admin-btn h-8 shrink-0 text-status-danger" data-action-url="{{ $api.'/biometric-keys/'.$key->public_id }}" data-action-method="DELETE" data-confirm-title="{{ __('admin.customers.revoke_key') }}" data-confirm-danger data-reason="{{ __('admin.common.reason') }}">{{ __('admin.common.revoke') }}</button>
                            @endif
                        </li>
                    @empty
                        <li class="text-content-muted">{{ __('admin.customers.no_keys') }}</li>
                    @endforelse
                </ul>
            </x-admin.card>

            @if ($manage)
                <x-admin.card :title="__('admin.customers.privacy')" :subtitle="__('admin.customers.privacy_note')">
                    <a href="{{ $api.'/export' }}" class="admin-btn" download><x-store.icon name="download" :size="16" /> {{ __('admin.customers.export') }}</a>
                    @if ($customer->status?->value !== 'closed')
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm font-medium text-status-danger">{{ __('admin.customers.erase') }}</summary>
                            <form class="mt-3 flex flex-col gap-3" data-module="form" data-action="{{ $api.'/anonymize' }}" data-method="POST" data-success="reload" data-confirm="{{ __('admin.customers.erase_confirm') }}" data-confirm-danger novalidate>
                                <p class="text-xs text-content-secondary">{{ __('admin.customers.erase_text') }}</p>
                                <x-admin.field name="reason" id="er-reason" :label="__('admin.common.reason')" maxlength="500" />
                                <x-admin.field name="confirm" id="er-confirm" :label="__('admin.customers.erase_type', ['id' => $customer->public_id])" dir="ltr" autocomplete="off" />
                                <x-admin.form-alerts />
                                <div><button type="submit" class="admin-btn-danger">{{ __('admin.customers.erase') }}</button></div>
                            </form>
                        </details>
                    @endif
                </x-admin.card>
            @endif
        </div>
    </div>
@endsection
