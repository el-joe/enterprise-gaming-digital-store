@extends('layouts.account')

@php
    $methods = ['stripe' => __('storefront.payment.card'), 'paypal' => 'PayPal', 'paymob' => 'Paymob', 'wallet' => __('storefront.payment.wallet')];
    $lastPayment = collect($order['payments'])->last();
    $revealUrl = url('/api/v1/account/orders/'.$order['public_id'].'/units/__unit__/reveal');
@endphp

@section('title', __('storefront.order.title', ['number' => $order['number']]))
@section('nav', 'orders')

@section('page')
    <div>
        <a href="{{ url()->localized('storefront.account.orders') }}" class="inline-flex items-center gap-1 text-sm text-content-secondary hover:text-content-primary"><x-store.icon name="chevron-back" :size="16" /> {{ __('account.orders.back') }}</a>
        <h1 class="mt-2 font-display text-[28px] leading-[34px] font-bold md:text-[32px] md:leading-[38px]">{{ __('storefront.order.title', ['number' => '']) }}<span dir="ltr">{{ $order['number'] }}</span></h1>
        <p class="mt-2 flex flex-wrap items-center gap-2 text-sm text-content-secondary">
            <x-account.status :status="$order['status']" />
            <span>{{ __('account.orders.placed') }} <x-account.time :at="$order['created_at']" /></span>
        </p>
    </div>

    @if ($canRetry)
        <div class="alert-warning" data-module="retry-payment"
            data-retry-url="{{ url('/api/v1/checkout/orders/'.$order['public_id'].'/retry-payment') }}"
            data-return-url="{{ route('storefront.checkout.return', ['orderPublicId' => $order['public_id']]) }}">
            <x-store.icon name="alert" class="mt-0.5 text-status-warning" />
            <div class="flex flex-1 flex-col gap-3">
                <p>{{ __('storefront.order.unpaid_text') }}</p>
                <div><button type="button" class="btn-primary" data-retry>{{ __('storefront.order.retry') }}</button></div>
                <p class="text-sm text-status-danger" data-retry-error hidden></p>
            </div>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
        <x-store.code-reveal :reveal-url="$revealUrl" class="flex min-w-0 flex-col gap-4">
            @foreach ($order['items'] as $item)
                @php($hasCodes = collect($item['units'])->contains(fn ($unit) => $unit['masked_code'] !== null))
                <section class="rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-5" aria-label="{{ $item['name'] }}">
                    <div class="flex items-center gap-3">
                        <x-store.tile :slug="$item['product']['slug'] ?? ''" class="h-12 w-[72px] shrink-0 rounded-md" />
                        <div class="min-w-0 flex-1">
                            <h2 class="font-semibold">{{ $item['name'] }} <span class="text-content-secondary" dir="ltr">× {{ $item['quantity'] }}</span></h2>
                            <p class="text-xs text-content-muted">{{ __('storefront.delivery.'.$item['delivery_type']) }}@if ($item['line_total']) · <span dir="ltr">{{ $item['line_total']['formatted'] }}</span>@endif</p>
                        </div>
                        @if ($item['product']['slug'] ?? null)
                            <a href="{{ url()->localized('storefront.products.show', ['slug' => $item['product']['slug']]) }}" class="hidden text-xs font-medium text-accent-text sm:inline">{{ __('account.orders.buy_again') }}</a>
                        @endif
                    </div>
                    <p class="mt-4 font-display text-xs leading-4 font-semibold tracking-[0.12em] text-content-muted uppercase">{{ $hasCodes ? __('account.orders.digital_codes') : __('account.orders.units') }}</p>
                    <div class="mt-2 flex flex-col gap-2">
                        @foreach ($item['units'] as $unit)
                            <x-store.code-unit :unit="$unit" :can-reveal="true" />
                        @endforeach
                    </div>
                    @if ($hasCodes)
                        <p class="mt-3 text-xs text-content-muted">{{ __('account.orders.no_returns') }}</p>
                    @endif
                </section>
            @endforeach
        </x-store.code-reveal>

        <aside class="flex flex-col gap-4">
            <section class="rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-5" aria-labelledby="timeline-title">
                <h2 id="timeline-title" class="text-lg font-semibold">{{ __('account.orders.progress') }}</h2>
                <ol class="mt-4 flex flex-col">
                    @foreach ($timeline as $event)
                        <li class="relative flex gap-3 pb-4 last:pb-0">
                            <span class="relative z-10 mt-1.5 size-2.5 shrink-0 rounded-full bg-accent-primary" aria-hidden="true"></span>
                            @unless ($loop->last)<span class="absolute start-[4.5px] top-4 bottom-0 w-px bg-line-default" aria-hidden="true"></span>@endunless
                            <div>
                                <p class="text-sm font-medium">{{ $event['label'] }}</p>
                                <p class="text-xs text-content-muted"><x-account.time :at="$event['at']" format="datetime" /></p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-5" aria-labelledby="payment-title">
                <h2 id="payment-title" class="text-lg font-semibold">{{ __('storefront.order.payment') }}</h2>
                <dl class="mt-4 flex flex-col gap-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('account.orders.display_price') }}</dt><dd class="font-semibold" dir="ltr">{{ $order['totals']['display']['formatted'] }}</dd></div>
                    @if ($order['totals']['payment'])
                        <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('storefront.checkout.payment_amount') }}</dt><dd class="font-display text-lg font-bold text-accent-text" dir="ltr">{{ $order['totals']['payment']['formatted'] }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('storefront.checkout.payment_currency') }}</dt><dd class="font-semibold">{{ $order['currency']['payment'] }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('account.orders.payment_status') }}</dt><dd>{{ __('account.orders.payment_states.'.$order['payment_status']) }}</dd></div>
                    @if ($lastPayment)
                        <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('storefront.order.method') }}</dt><dd>{{ $methods[$lastPayment['provider']] ?? $lastPayment['provider'] }}</dd></div>
                    @endif
                    @foreach ($order['refunds'] as $refund)
                        <div class="flex justify-between gap-3 border-t border-line-subtle pt-3"><dt class="text-content-secondary">{{ __('account.orders.refund') }} · {{ __('account.orders.refund_destinations.'.$refund['destination']) }}</dt><dd><span dir="ltr">{{ $refund['amount']['formatted'] ?? '' }}</span> · {{ __('account.orders.refund_statuses.'.$refund['status']) }}</dd></div>
                    @endforeach
                </dl>
            </section>

            @if ($canRequestRefund)
                <details class="group rounded-xl border border-line-subtle bg-surface-raised p-4 md:p-5">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 font-semibold">
                        {{ __('account.refund.title') }}
                        <x-store.icon name="chevron-down" :size="18" class="transition group-open:rotate-180" />
                    </summary>
                    <form class="mt-4 flex flex-col gap-3" data-module="account-form" data-action="{{ url('/api/v1/account/refund-requests') }}" data-method="POST" data-success="reload" novalidate>
                        <input type="hidden" name="order_public_id" value="{{ $order['public_id'] }}">
                        <p class="text-sm text-content-secondary">{{ __('account.refund.text') }}</p>
                        <div>
                            <label for="refund-reason" class="field-label">{{ __('account.refund.reason') }}</label>
                            <textarea id="refund-reason" name="reason" rows="4" minlength="10" maxlength="2000" class="field-input py-2.5" aria-describedby="refund-reason-error" required></textarea>
                            <p id="refund-reason-error" class="field-error" data-error-for="reason" hidden></p>
                        </div>
                        <div class="alert-danger" data-form-error hidden role="alert"><p data-form-error-text></p></div>
                        <button type="submit" class="btn-secondary">{{ __('account.refund.submit') }}</button>
                    </form>
                </details>
            @elseif ($refundRequested)
                <div class="alert-info"><x-store.icon name="clock" class="mt-0.5 text-status-info" /><p>{{ __('account.refund.requested') }}</p></div>
            @endif

            <a href="{{ url()->localized('storefront.account.tickets.create') }}?order={{ $order['public_id'] }}" class="btn-secondary w-full"><x-store.icon name="chat" :size="18" /> {{ __('account.orders.need_help') }}</a>
        </aside>
    </div>
@endsection
