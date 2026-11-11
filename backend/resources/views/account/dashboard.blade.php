@extends('layouts.account')

@section('title', __('account.dashboard.title'))
@section('nav', 'dashboard')

@section('page')
    <x-account.page-head :title="__('account.dashboard.welcome', ['name' => $user->name])">
    </x-account.page-head>
    <p class="-mt-4 text-sm text-content-secondary">
        <span dir="ltr">{{ $user->email }}</span> ·
        {{ $user->hasVerifiedEmail() ? __('account.dashboard.email_verified') : __('account.dashboard.email_unverified') }}
    </p>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4">
        <a href="{{ url()->localized('storefront.account.orders') }}" class="flex flex-col gap-1 rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 hover:border-line-strong">
            <span class="flex items-center gap-2 text-xs text-content-secondary"><x-store.icon name="box" :size="16" class="text-accent-text" /> {{ __('account.dashboard.orders') }}</span>
            <span class="font-display text-[28px] leading-9 font-bold" dir="ltr">{{ $counts['orders'] }}</span>
            <span class="text-xs text-content-muted">{{ trans_choice('account.dashboard.in_progress', $counts['in_progress'], ['count' => $counts['in_progress']]) }}</span>
        </a>
        <a href="{{ url()->localized('storefront.account.wallet') }}" class="flex flex-col gap-1 rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 hover:border-line-strong">
            <span class="flex items-center gap-2 text-xs text-content-secondary"><x-store.icon name="wallet" :size="16" class="text-accent-text" /> {{ __('account.dashboard.wallet') }}</span>
            <span class="font-display text-[28px] leading-9 font-bold" dir="ltr">{{ $wallet['balance']['formatted'] }}</span>
            <span class="text-xs text-content-muted">{{ __('account.wallet.credits_only') }}</span>
        </a>
        <a href="{{ url()->localized('storefront.account.codes') }}?state=sealed" class="flex flex-col gap-1 rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 hover:border-line-strong">
            <span class="flex items-center gap-2 text-xs text-content-secondary"><x-store.icon name="key" :size="16" class="text-accent-text" /> {{ __('account.dashboard.sealed_codes') }}</span>
            <span class="font-display text-[28px] leading-9 font-bold" dir="ltr">{{ $counts['sealed_codes'] }}</span>
            <span class="text-xs text-content-muted">{{ __('account.dashboard.sealed_hint') }}</span>
        </a>
    </div>

    @if ($sealed)
        <div class="alert-info">
            <x-store.icon name="info" class="mt-0.5 text-status-info" />
            <div>
                <p class="font-semibold">{{ __('account.dashboard.code_ready') }}</p>
                <p class="text-content-secondary">
                    {{ __('account.dashboard.code_ready_text', ['name' => $sealed['name'], 'number' => $sealed['order_number']]) }}
                    <a href="{{ url()->localized('storefront.account.orders.show', ['orderPublicId' => $sealed['order_public_id']]) }}" class="font-medium text-accent-text underline underline-offset-2">{{ __('account.dashboard.open_order') }}</a>
                </p>
            </div>
        </div>
    @endif

    <section aria-labelledby="recent-orders" class="flex flex-col gap-3">
        <div class="flex items-center justify-between gap-3">
            <h2 id="recent-orders" class="text-xl font-semibold">{{ __('account.dashboard.recent_orders') }}</h2>
            @if ($orders !== [])
                <a href="{{ url()->localized('storefront.account.orders') }}" class="text-sm font-medium text-accent-text">{{ __('account.common.view_all') }}</a>
            @endif
        </div>
        @if ($orders === [])
            <x-store.empty-state art="no-orders" :title="__('account.orders.empty_title')" :text="__('account.orders.empty_text')" class="rounded-xl border border-line-subtle bg-surface-raised">
                <a href="{{ url()->localized('storefront.home') }}" class="btn-primary">{{ __('account.common.start_shopping') }}</a>
            </x-store.empty-state>
        @else
            @include('account.partials.orders-table', ['orders' => $orders])
        @endif
    </section>

    @if ($picked !== [])
        <section aria-labelledby="picked" class="flex flex-col gap-3">
            <h2 id="picked" class="text-xl font-semibold">{{ __('account.dashboard.picked') }}</h2>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4">
                @foreach ($picked as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
