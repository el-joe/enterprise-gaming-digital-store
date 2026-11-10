@extends('layouts.storefront')

@section('title', __('storefront.access.title'))
@section('robots', 'noindex,nofollow')

@section('content')
    <div class="container-page max-w-xl py-12 md:py-20">
        <h1 class="font-display text-[32px] leading-[38px] font-bold">{{ __('storefront.access.title') }}</h1>
        <p class="mt-3 text-content-secondary">{{ __('storefront.access.intro') }}</p>

        <div class="keycut mt-8 border border-line-subtle bg-surface-raised p-5 md:p-6" data-module="guest-access"
            data-order="{{ $orderPublicId }}"
            data-send-url="{{ url('/api/v1/checkout/guest/otp/send') }}"
            data-verify-url="{{ url('/api/v1/checkout/guest/otp/verify') }}">
            <form class="flex flex-col gap-4" data-otp-send novalidate>
                <x-store.field name="email" type="email" :label="__('storefront.access.email')" autocomplete="email" required dir="ltr" />
                <button type="submit" class="btn-primary">{{ __('storefront.access.send') }}</button>
                <p class="text-sm text-content-secondary" data-send-message hidden role="status"></p>
            </form>
            <form class="mt-6 flex flex-col gap-4 border-t border-line-subtle pt-6" data-otp-verify novalidate>
                <x-store.field name="code" :label="__('storefront.access.code')" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required dir="ltr" class="font-mono tracking-[0.4em]" />
                <button type="submit" class="btn-secondary">{{ __('storefront.access.verify') }}</button>
                <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5" /><p data-form-error-text></p></div>
            </form>
        </div>
    </div>
@endsection
