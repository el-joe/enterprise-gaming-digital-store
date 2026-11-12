@extends('layouts.storefront')

@php
    $paid = in_array($order['payment_status'], ['paid', 'partially_refunded', 'refunded'], true);
    $state = match (true) {
        $order['status'] === 'completed' => 'completed',
        in_array($order['status'], ['failed', 'cancelled'], true) => 'closed',
        in_array($order['status'], ['refunded', 'partially_refunded', 'refund_pending'], true) => 'refunded',
        $paid => 'paid',
        $order['payment_status'] === 'processing' => 'processing',
        default => 'unpaid',
    };
    $methods = ['stripe' => __('storefront.payment.card'), 'paypal' => 'PayPal', 'paymob' => 'Paymob', 'wallet' => __('storefront.payment.wallet')];
    $lastPayment = collect($order['payments'])->last();
@endphp

@section('title', __('storefront.order.title', ['number' => $order['number']]))
@section('robots', 'noindex,nofollow')
@section('header', 'minimal')

@section('content')
    <div class="container-page max-w-[800px] py-10 md:py-14">
        <span @class([
            'inline-flex size-16 items-center justify-center rounded-se-[28px] rounded-ss-md rounded-es-md rounded-ee-md',
            'bg-status-success-tint text-status-success' => in_array($state, ['paid', 'completed'], true),
            'bg-status-info-tint text-status-info' => in_array($state, ['processing', 'unpaid', 'refunded'], true),
            'bg-status-danger-tint text-status-danger' => $state === 'closed',
        ]) aria-hidden="true">
            <x-store.icon name="{{ in_array($state, ['paid', 'completed'], true) ? 'check' : ($state === 'closed' ? 'x' : 'clock') }}" :size="28" />
        </span>
        <h1 class="mt-6 font-display text-[28px] leading-[34px] font-bold md:text-[32px] md:leading-[38px]">{{ __('storefront.order.state.'.$state) }}</h1>
        <p class="mt-2 text-sm text-content-secondary">
            {{ __('storefront.order.number', ['number' => $order['number']]) }}
            @if ($email) · {{ __('storefront.order.sent_to') }} <span dir="ltr">{{ $email }}</span>@endif
        </p>

        @include('storefront.partials.steps', ['current' => $paid ? 3 : 2])

        @if ($state === 'unpaid' && ! $order['is_final'])
            <div class="alert-warning mt-6" data-module="retry-payment"
                data-retry-url="{{ url('/api/v1/checkout/orders/'.$order['public_id'].'/retry-payment') }}"
                data-return-url="{{ route('storefront.checkout.return', ['orderPublicId' => $order['public_id']]) }}">
                <x-store.icon name="alert" class="mt-0.5 text-status-warning" />
                <div class="flex flex-1 flex-col gap-3">
                    <p>{{ __('storefront.order.unpaid_text') }}</p>
                    <div>
                        <button type="button" class="btn-primary" data-retry>{{ __('storefront.order.retry') }}</button>
                    </div>
                    <p class="text-sm text-status-danger" data-retry-error hidden></p>
                </div>
            </div>
        @endif

        <div class="alert-info mt-6">
            <x-store.icon name="info" class="mt-0.5 text-status-info" />
            <div>
                <p class="font-semibold">{{ __('storefront.order.sealed_title') }}</p>
                <p class="text-content-secondary">{{ __('storefront.order.sealed_text') }}</p>
            </div>
        </div>

        <x-store.code-reveal class="keycut mt-4 block border border-line-subtle bg-surface-raised p-5 md:p-6" aria-labelledby="items-title" :guest="$isGuest"
            :reveal-url="$isGuest ? url('/api/v1/checkout/guest/orders/'.$order['public_id'].'/units/__unit__/reveal') : url('/api/v1/account/orders/'.$order['public_id'].'/units/__unit__/reveal')">
            <h2 id="items-title" class="text-xl font-semibold">{{ __('storefront.order.items') }}</h2>
            <ul class="mt-4 flex flex-col gap-3">
                @foreach ($order['items'] as $item)
                    <li class="rounded-md bg-surface-base p-3">
                        <div class="flex items-center gap-3">
                            <x-store.product-image :slug="($item['product'] ?? [])['slug'] ?? ''" :name="$item['name']" :image="(($item['product'] ?? [])['image_source'] ?? null) === 'product' ? ($item['product'] ?? [])['image'] : null" :logo="(($item['product'] ?? [])['image_source'] ?? null) === 'brand' ? ($item['product'] ?? [])['image'] : null" class="h-12 w-[72px] shrink-0" data-line-image />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold">{{ $item['name'] }} <span class="text-content-secondary">× {{ $item['quantity'] }}</span></p>
                                <p class="text-xs text-content-muted" dir="ltr">{{ $item['line_total']['formatted'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-col gap-2">
                            @foreach ($item['units'] as $unit)
                                <x-store.code-unit :unit="$unit" :can-reveal="$canReveal" />
                                @if ($unit['revealable'] && ! $canReveal && $isGuest)
                                    <p class="text-xs text-content-muted">{{ __('storefront.order.reveal_needs_link') }}</p>
                                @endif
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-store.code-reveal>

        <section class="keycut mt-4 border border-line-subtle bg-surface-raised p-5 md:p-6" aria-labelledby="payment-summary-title">
            <h2 id="payment-summary-title" class="text-xl font-semibold">{{ __('storefront.order.payment') }}</h2>
            <dl class="mt-4 flex flex-col gap-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('storefront.order.display_total') }}</dt><dd class="font-semibold" dir="ltr">{{ $order['totals']['display']['formatted'] }}</dd></div>
                @if ($order['totals']['payment'])
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('storefront.checkout.payment_amount') }}</dt><dd class="font-display text-lg font-bold text-accent-text" dir="ltr">{{ $order['totals']['payment']['formatted'] }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('storefront.checkout.payment_currency') }}</dt><dd><span class="rounded-xs bg-accent-tint px-2 py-0.5 text-xs font-semibold text-accent-text">{{ $order['currency']['payment'] }}</span></dd></div>
                @endif
                @if ($lastPayment)
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('storefront.order.method') }}</dt><dd>{{ $methods[$lastPayment['provider']] ?? $lastPayment['provider'] }}</dd></div>
                @endif
                <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('storefront.order.status') }}</dt><dd>{{ Lang::has('storefront.order.status_value.'.$order['status']) ? __('storefront.order.status_value.'.$order['status']) : \Illuminate\Support\Str::headline($order['status']) }}</dd></div>
            </dl>
        </section>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ url()->localized('storefront.home') }}" class="btn-secondary">{{ __('storefront.common.back_to_store') }}</a>
            @unless ($isGuest)
                <a href="{{ url()->localized('storefront.account.orders.show', ['orderPublicId' => $order['public_id']]) }}" class="btn-ghost">{{ __('account.orders.open_in_account') }}</a>
            @endunless
        </div>
    </div>
@endsection
