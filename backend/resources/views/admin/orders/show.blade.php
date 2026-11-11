@extends('layouts.admin')

@section('title', __('admin.orders.order', ['number' => $order['number']]))
@section('heading', __('admin.orders.order', ['number' => $order['number']]))

@section('breadcrumbs')
    <x-admin.crumb :href="route('admin.orders.index')">{{ __('admin.nav.orders') }}</x-admin.crumb>
    <x-admin.crumb><span class="font-mono">{{ $order['number'] }}</span></x-admin.crumb>
@endsection

@php
    $api = fn (string $path): string => url('/admin/api/orders/'.$order['public_id'].'/'.$path);
    $actions = $order['actions'];
    $needsReconcile = $order['open_attempts'] > 0;
@endphp

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <x-admin.status group="order_status" :value="$order['status']" />
        <p class="text-sm text-content-secondary">{{ __('admin.orders.placed', ['date' => $order['placed_at']?->format('Y-m-d H:i')]) }}</p>
        @if ($order['paid_at'])<p class="text-sm text-content-secondary">· {{ __('admin.orders.paid_at', ['date' => $order['paid_at']->format('Y-m-d H:i')]) }}</p>@endif
    </div>

    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="flex min-w-0 flex-col gap-4 md:gap-6" data-module="action">
            @if ($needsReconcile)
                <section class="rounded-lg border border-status-warning/60 bg-status-warning-tint/30 p-5" aria-labelledby="reconcile-title">
                    <h2 id="reconcile-title" class="flex items-center gap-2 text-lg font-semibold"><x-store.icon name="alert" class="text-status-warning" /> {{ __('admin.orders.reconcile_title') }}</h2>
                    <p class="mt-2 text-sm text-content-secondary">{{ __('admin.orders.reconcile_text') }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($actions['reconcile'])
                            <button type="button" class="admin-btn-primary" data-action-url="{{ $api('reconcile') }}" data-confirm-title="{{ __('admin.orders.reconcile_confirm') }}" data-confirm-label="{{ __('admin.orders.check_now') }}"><x-store.icon name="refresh" :size="16" /> {{ __('admin.orders.check_now') }}</button>
                        @endif
                        <button type="button" class="admin-btn" disabled title="{{ __('admin.orders.retry_locked_help') }}"><x-store.icon name="lock" :size="16" /> {{ __('admin.orders.retry_locked') }}</button>
                    </div>
                    <p class="mt-3 text-xs text-content-muted">{{ $actions['reconcile'] ? __('admin.orders.reconcile_note') : __('admin.orders.needs_permission', ['permission' => 'orders.reconcile']) }}</p>
                </section>
            @endif

            @if ($order['status'] === 'manual_review')
                <section class="rounded-lg border border-line-default bg-surface-raised p-5" aria-labelledby="review-title">
                    <h2 id="review-title" class="text-lg font-semibold">{{ __('admin.orders.review_title') }}</h2>
                    <p class="mt-1 text-sm text-content-secondary">{{ __('admin.orders.review_text') }}</p>
                    @if ($order['risk'])
                        <p class="mt-2 text-sm">{{ __('admin.orders.risk_summary', ['score' => $order['risk']['score']]) }} <x-admin.status group="risk_decision" :value="$order['risk']['decision']" /></p>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($actions['approve'])
                            <button type="button" class="admin-btn-primary" data-action-url="{{ $api('review') }}" data-confirm-title="{{ __('admin.orders.approve_confirm') }}" data-confirm-label="{{ __('admin.orders.approve') }}" data-reason="{{ __('admin.orders.reason') }}"><x-store.icon name="check" :size="16" /> {{ __('admin.orders.approve') }}</button>
                        @endif
                        @if ($actions['reject'])
                            <button type="button" class="admin-btn-danger" data-action-url="{{ $api('reject') }}" data-confirm-title="{{ __('admin.orders.reject_confirm') }}" data-confirm-text="{{ __('admin.orders.reject_text') }}" data-confirm-label="{{ __('admin.orders.reject') }}" data-confirm-danger data-reason="{{ __('admin.orders.reason') }}" data-action-body='{"refund_reason":"fraud"}'><x-store.icon name="x" :size="16" /> {{ __('admin.orders.reject') }}</button>
                        @endif
                    </div>
                    @unless ($actions['approve'] || $actions['reject'])
                        <p class="mt-3 text-xs text-content-muted">{{ __('admin.orders.needs_permission', ['permission' => 'orders.review']) }}</p>
                    @endunless
                </section>
            @endif

            <x-admin.card :title="__('admin.orders.units')" :padded="false">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr>
                            <th class="admin-th">{{ __('admin.orders.unit') }}</th>
                            <th class="admin-th">{{ __('admin.orders.product') }}</th>
                            <th class="admin-th">{{ __('admin.orders.col.status') }}</th>
                            <th class="admin-th">{{ __('admin.orders.supplier_ref') }}</th>
                            <th class="admin-th">{{ __('admin.orders.code') }}</th>
                            <th class="admin-th"><span class="sr-only">{{ __('admin.common.actions') }}</span></th>
                        </tr></thead>
                        <tbody>
                            @foreach ($order['units'] as $index => $unit)
                                <tr>
                                    <td class="admin-td font-mono text-xs">U-{{ $index + 1 }}</td>
                                    <td class="admin-td">{{ $unit['product'] }}@if ($unit['sku'])<span class="block font-mono text-xs text-content-muted">{{ $unit['sku'] }}</span>@endif</td>
                                    <td class="admin-td"><x-admin.status group="unit_status" :value="$unit['status']" /></td>
                                    <td class="admin-td font-mono text-xs text-content-secondary">{{ $unit['reference'] ?? '—' }}@if ($unit['supplier'])<span class="block font-sans text-content-muted">{{ $unit['supplier'] }}</span>@endif</td>
                                    <td class="admin-td font-mono text-xs" dir="ltr">{{ $unit['masked_code'] ?? '—' }}</td>
                                    <td class="admin-td text-end">
                                        @if ($actions['retry'] && $unit['retryable'])
                                            <button type="button" class="admin-btn" data-action-url="{{ $api('retry-verified-unit') }}" data-action-body='{"unit_public_id":"{{ $unit['public_id'] }}"}' data-confirm-title="{{ __('admin.orders.retry_confirm') }}" data-reason="{{ __('admin.orders.reason') }}">{{ __('admin.orders.retry') }}</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-admin.card>

            @if ($order['attempts'] !== [])
                <x-admin.card :title="__('admin.orders.attempts')" :subtitle="__('admin.orders.attempts_note')" :padded="false">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead><tr>
                                <th class="admin-th">{{ __('admin.orders.attempt') }}</th>
                                <th class="admin-th">{{ __('admin.orders.supplier') }}</th>
                                <th class="admin-th">{{ __('admin.orders.col.status') }}</th>
                                <th class="admin-th">{{ __('admin.orders.reference') }}</th>
                                <th class="admin-th">{{ __('admin.orders.next_check') }}</th>
                                <th class="admin-th text-end">{{ __('admin.orders.cost') }}</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($order['attempts'] as $attempt)
                                    <tr>
                                        <td class="admin-td">#{{ $attempt['attempt_no'] }}@if ($attempt['is_fallback']) <x-admin.badge tone="info" :dot="false">{{ __('admin.orders.fallback') }}</x-admin.badge>@endif</td>
                                        <td class="admin-td">{{ $attempt['supplier'] ?? '—' }}</td>
                                        <td class="admin-td"><x-admin.status group="transaction_status" :value="$attempt['status']" />@if ($attempt['outcome'])<span class="mt-1 block text-xs text-content-muted">{{ $attempt['outcome'] }}</span>@endif</td>
                                        <td class="admin-td font-mono text-xs">{{ $attempt['reference'] }}</td>
                                        <td class="admin-td text-xs text-content-secondary">{{ $attempt['next_check_at']?->format('Y-m-d H:i') ?? '—' }}@if ($attempt['deadline_at'])<span class="block text-content-muted">{{ __('admin.orders.deadline', ['date' => $attempt['deadline_at']->format('Y-m-d H:i')]) }}</span>@endif</td>
                                        <td class="admin-td text-end" dir="ltr">{{ $attempt['cost'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($actions['mark_not_accepted'])
                        @foreach (collect($order['attempts'])->where('unresolved', true) as $attempt)
                            <details class="mx-5 mb-4 rounded-md border border-line-subtle bg-surface-canvas p-3">
                                <summary class="cursor-pointer text-sm font-medium">{{ __('admin.orders.mark_not_accepted') }} · #{{ $attempt['attempt_no'] }} <span class="font-mono text-xs text-content-muted">{{ $attempt['reference'] }}</span></summary>
                                <form class="mt-3 grid gap-3 md:grid-cols-2" data-module="form" data-action="{{ url('/admin/api/transactions/'.$attempt['public_id'].'/mark-not-accepted') }}" data-success="reload" data-confirm="{{ __('admin.orders.mark_not_accepted_confirm') }}" data-confirm-danger novalidate>
                                    <x-admin.field name="reason" :id="'mna-reason-'.$attempt['attempt_no'].'-'.$loop->index" :label="__('admin.orders.reason')" maxlength="1000" />
                                    <x-admin.field name="evidence" :id="'mna-evidence-'.$loop->index" :label="__('admin.orders.evidence')" maxlength="2000" :help="__('admin.orders.evidence_help')" />
                                    <div class="md:col-span-2"><x-admin.form-alerts /></div>
                                    <div class="md:col-span-2"><button type="submit" class="admin-btn-danger">{{ __('admin.orders.mark_not_accepted') }}</button></div>
                                </form>
                            </details>
                        @endforeach
                    @endif
                </x-admin.card>
            @endif

            <x-admin.card :title="__('admin.orders.payments')" :padded="false">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr>
                            <th class="admin-th">{{ __('admin.payments.col.reference') }}</th>
                            <th class="admin-th">{{ __('admin.payments.col.gateway') }}</th>
                            <th class="admin-th">{{ __('admin.payments.col.status') }}</th>
                            <th class="admin-th text-end">{{ __('admin.payments.col.amount') }}</th>
                            <th class="admin-th">{{ __('admin.payments.col.verified') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($order['payments'] as $payment)
                                <tr>
                                    <td class="admin-td font-mono text-xs">{{ \App\DataTables\Payments\PaymentReference::short($payment['reference']) ?? '—' }}@if ($payment['primary']) <x-admin.badge tone="accent" :dot="false">{{ __('admin.orders.primary') }}</x-admin.badge>@endif</td>
                                    <td class="admin-td">{{ $payment['gateway'] }} · #{{ $payment['attempt_no'] }}</td>
                                    <td class="admin-td"><x-admin.status group="payment_status" :value="$payment['status']" />@if ($payment['failure'])<span class="mt-1 block text-xs text-content-muted">{{ $payment['failure'] }}</span>@endif</td>
                                    <td class="admin-td text-end font-semibold" dir="ltr">{{ $payment['amount'] }}</td>
                                    <td class="admin-td text-xs text-content-secondary">{{ $payment['verified_at']?->format('Y-m-d H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="admin-td text-center text-content-muted">{{ __('admin.orders.no_payments') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>

            <x-admin.card :title="__('admin.orders.refunds')">
                @if ($order['refunds'] !== [])
                    <ul class="mb-4 flex flex-col divide-y divide-line-subtle">
                        @foreach ($order['refunds'] as $refund)
                            <li class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
                                <span><x-admin.status group="refund_status" :value="$refund['status']" /> {{ \App\DataTables\Cell::label('refund_reason', $refund['reason']) }} · {{ \App\DataTables\Cell::label('refund_destination', $refund['destination']) }}</span>
                                <span class="font-semibold" dir="ltr">{{ $refund['amount'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($actions['refund'])
                    <form class="flex flex-col gap-4" data-module="form" data-action="{{ $api('refunds') }}" data-success="reload" data-confirm="{{ __('admin.orders.refund_confirm') }}" data-confirm-text="{{ __('admin.orders.refund_text') }}" data-confirm-danger novalidate>
                        <fieldset>
                            <legend class="field-label">{{ __('admin.orders.refund_units') }}</legend>
                            <div class="flex flex-col gap-1.5">
                                @foreach ($order['units'] as $index => $unit)
                                    <label @class(['flex items-center gap-2 text-sm', 'text-content-muted' => ! $unit['refundable']])>
                                        <input type="checkbox" name="unit_ids[]" value="{{ $unit['public_id'] }}" class="size-4 accent-(--color-accent-text)" @disabled(! $unit['refundable'])>
                                        U-{{ $index + 1 }} · {{ $unit['product'] }} · {{ \App\DataTables\Cell::label('unit_status', $unit['status']) }}
                                    </label>
                                @endforeach
                            </div>
                            <p class="field-error" data-error-for="unit_ids" hidden></p>
                        </fieldset>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label for="refund-reason" class="field-label">{{ __('admin.orders.refund_reason') }}</label>
                                <select id="refund-reason" name="reason" class="admin-input">
                                    @foreach ($refundReasons as $reason)
                                        <option value="{{ $reason->value }}">{{ \App\DataTables\Cell::label('refund_reason', $reason->value) }}</option>
                                    @endforeach
                                </select>
                                <p class="field-error" data-error-for="reason" hidden></p>
                            </div>
                            <div>
                                <label for="refund-destination" class="field-label">{{ __('admin.orders.refund_destination') }}</label>
                                <select id="refund-destination" name="destination" class="admin-input">
                                    <option value="original_method">{{ \App\DataTables\Cell::label('refund_destination', 'original_method') }}</option>
                                    @if ($order['customer']['registered'])<option value="wallet">{{ \App\DataTables\Cell::label('refund_destination', 'wallet') }}</option>@endif
                                </select>
                                <p class="field-error" data-error-for="destination" hidden></p>
                            </div>
                        </div>
                        <x-admin.field name="note" :label="__('admin.orders.refund_note')" maxlength="2000" :help="__('admin.common.audited')" />
                        <x-admin.form-alerts />
                        <div><button type="submit" class="admin-btn-danger">{{ __('admin.orders.refund') }}</button></div>
                    </form>
                @elseif ($order['refunds'] === [])
                    <p class="text-sm text-content-muted">{{ __('admin.orders.no_refunds') }}</p>
                @endif
            </x-admin.card>

            <x-admin.card :title="__('admin.orders.timeline')">
                <x-slot:actions><span class="text-xs text-content-muted">{{ __('admin.orders.append_only') }}</span></x-slot:actions>
                <ol class="relative flex flex-col gap-4 border-s border-line-default ps-5">
                    @forelse ($order['events'] as $event)
                        <li class="relative">
                            <span @class([
                                'absolute -start-[26px] top-1.5 size-2.5 rounded-full ring-4 ring-surface-raised',
                                'bg-status-success' => in_array($event['to'], ['completed', 'paid'], true),
                                'bg-status-warning' => in_array($event['to'], ['pending_verification', 'manual_review', 'partially_fulfilled', 'refund_pending'], true),
                                'bg-status-danger' => in_array($event['to'], ['failed', 'cancelled'], true),
                                'bg-status-info' => $event['to'] !== null && ! in_array($event['to'], ['completed', 'paid', 'pending_verification', 'manual_review', 'partially_fulfilled', 'refund_pending', 'failed', 'cancelled'], true),
                                'bg-line-strong' => $event['to'] === null,
                            ]) aria-hidden="true"></span>
                            <p class="text-sm font-medium">{{ $event['to'] ? __('admin.orders.event_status', ['status' => \App\DataTables\Cell::label('order_status', $event['to'])]) : \Illuminate\Support\Str::headline(str_replace('.', ' ', $event['type'])) }}</p>
                            <p class="text-xs text-content-muted"><span dir="ltr">{{ $event['at']?->format('Y-m-d H:i:s') }}</span> · {{ \App\DataTables\Cell::label('actor', $event['actor']) }}@if ($event['message']) · {{ $event['message'] }}@endif</p>
                        </li>
                    @empty
                        <li class="text-sm text-content-muted">{{ __('admin.orders.no_events') }}</li>
                    @endforelse
                </ol>
            </x-admin.card>
        </div>

        <aside class="flex flex-col gap-4 md:gap-6">
            <x-admin.card :title="__('admin.orders.snapshot')">
                <dl class="flex flex-col gap-2.5 text-sm">
                    @foreach ([
                        'display_total' => $order['snapshot']['display_total'],
                        'payment_total' => $order['snapshot']['payment_total'],
                        'payment_currency' => $order['snapshot']['payment_currency'],
                        'base_total' => $order['snapshot']['base_total'],
                        'discount' => $order['snapshot']['discount'],
                        'gateway' => $order['snapshot']['gateway'],
                        'rate' => $order['snapshot']['rate'] ? $order['snapshot']['rate'].($order['snapshot']['rate_source'] ? ' · '.strtoupper($order['snapshot']['rate_source']) : '') : null,
                        'coupon' => $order['snapshot']['coupon'],
                    ] as $key => $value)
                        <div class="flex justify-between gap-3">
                            <dt class="text-content-secondary">{{ __('admin.orders.snap.'.$key) }}</dt>
                            <dd @class(['text-end font-semibold', 'text-accent-text' => $key === 'payment_total']) dir="ltr">{{ $value ?? '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-admin.card>

            <x-admin.card :title="__('admin.orders.customer')">
                <dl class="flex flex-col gap-2.5 text-sm">
                    <div><dt class="sr-only">{{ __('admin.orders.col.customer') }}</dt><dd class="font-semibold">{{ $order['customer']['name'] ?? __('admin.orders.guest') }}</dd></div>
                    <div><dt class="sr-only">{{ __('admin.auth.email') }}</dt><dd class="text-content-secondary" dir="ltr">{{ $order['customer']['email'] ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.orders.account') }}</dt><dd>{{ $order['customer']['registered'] ? __('admin.orders.registered') : __('admin.orders.guest') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.orders.country') }}</dt><dd>{{ $order['customer']['country'] ?? '—' }} / {{ __('admin.orders.ip_country') }} {{ $order['customer']['ip_country'] ?? '—' }}</dd></div>
                </dl>
            </x-admin.card>

            <x-admin.card :title="__('admin.orders.risk')">
                @if ($order['risk'])
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="text-content-secondary">{{ __('admin.orders.risk_score') }}</span>
                        <x-admin.status group="risk_decision" :value="$order['risk']['decision']">{{ $order['risk']['score'] }}</x-admin.status>
                    </div>
                    <p class="mt-1 text-end text-xs text-content-muted">{{ __('admin.orders.score', ['score' => $order['risk']['score']]) }}</p>
                    @if ($order['risk']['signals'] !== [])
                        <ul class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($order['risk']['signals'] as $signal)<li><x-admin.badge :dot="false">{{ $signal }}</x-admin.badge></li>@endforeach
                        </ul>
                    @endif
                @else
                    <p class="text-sm text-content-muted">{{ __('admin.orders.no_risk') }}</p>
                @endif
            </x-admin.card>
        </aside>
    </div>
@endsection
