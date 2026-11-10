@extends('layouts.storefront')

@section('title', __('storefront.return.title'))
@section('robots', 'noindex,nofollow')
@section('header', 'minimal')

@section('content')
    <div class="container-page max-w-3xl py-12 md:py-20">
        @if ($order === null)
            <div class="flex flex-col items-start gap-4">
                <h1 class="font-display text-[32px] leading-[38px] font-bold">{{ __('storefront.return.unknown_title') }}</h1>
                <p class="text-content-secondary">{{ __('storefront.return.unknown_text') }}</p>
                <a href="{{ url()->localized('storefront.home') }}" class="btn-primary">{{ __('storefront.common.back_to_store') }}</a>
            </div>
        @else
            <div class="flex flex-col items-start gap-4" data-module="payment-return"
                data-status-url="{{ url('/api/v1/checkout/orders/'.$order['public_id'].'/status') }}"
                data-verify-url="{{ url('/api/v1/checkout/orders/'.$order['public_id'].'/verify-payment') }}"
                data-capture-url="{{ url('/api/v1/checkout/orders/'.$order['public_id'].'/paypal/capture') }}"
                data-order-url="{{ url()->localized('storefront.orders.show', ['orderPublicId' => $order['public_id']]) }}"
                data-gateway="{{ $gateway }}" data-payment-status="{{ $order['payment_status'] }}" data-final="{{ $order['is_final'] ? '1' : '0' }}"
                data-cancelled="{{ ($cancelled ?? false) ? '1' : '0' }}">
                <span class="inline-flex size-16 items-center justify-center rounded-se-[28px] rounded-ss-md rounded-es-md rounded-ee-md bg-status-info-tint text-status-info" aria-hidden="true">
                    <x-store.icon name="clock" :size="28" />
                </span>
                <h1 class="font-display text-[32px] leading-[38px] font-bold" data-return-title>{{ __('storefront.return.checking_title') }}</h1>
                <p class="text-content-secondary" data-return-text aria-live="polite">{{ __('storefront.return.checking_text') }}</p>
                <div class="h-1.5 w-full max-w-sm overflow-hidden rounded-full bg-surface-overlay" aria-hidden="true">
                    <div class="h-full w-1/3 animate-pulse rounded-full bg-accent-primary"></div>
                </div>
                <a href="{{ url()->localized('storefront.orders.show', ['orderPublicId' => $order['public_id']]) }}" class="btn-secondary" data-return-link>{{ __('storefront.return.view_order') }}</a>
            </div>
        @endif
    </div>
@endsection
