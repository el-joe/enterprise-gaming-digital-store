@extends('layouts.storefront')

@section('title', __('storefront.account.title'))
@section('robots', 'noindex,nofollow')

@section('content')
    <div class="container-page max-w-3xl pt-10 md:pt-14">
        <h1 class="font-display text-[32px] leading-[38px] font-bold">{{ __('storefront.account.hello', ['name' => $user->name]) }}</h1>
        <p class="mt-2 text-sm text-content-secondary" dir="ltr">{{ $user->email }}</p>

        @unless ($user->hasVerifiedEmail())
            <div class="alert-warning mt-6">
                <x-store.icon name="mail" class="mt-0.5 text-status-warning" />
                <p>{{ __('storefront.account.unverified') }} <a href="{{ url()->localized('storefront.verify-email') }}" class="text-accent-text underline">{{ __('storefront.account.verify_now') }}</a></p>
            </div>
        @endunless

        <div class="keycut mt-8 border border-line-subtle bg-surface-raised p-6">
            <h2 class="text-xl font-semibold">{{ __('storefront.account.soon_title') }}</h2>
            <p class="mt-2 text-sm text-content-secondary">{{ __('storefront.account.soon_text') }}</p>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ url()->localized('storefront.cart') }}" class="btn-secondary"><x-store.icon name="bag" :size="18" /> {{ __('storefront.cart.title') }}</a>
            <button type="button" class="btn-ghost" data-module="logout" data-logout><x-store.icon name="logout" :size="18" /> {{ __('storefront.account.sign_out') }}</button>
        </div>
    </div>
@endsection
