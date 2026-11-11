{{--
    Account navigation (Figma "Account Sidebar"): a sticky card with the volt indicator on the
    current item from lg up; a horizontally scrolling row of pills below lg.
--}}
@props(['current' => 'dashboard', 'unread' => 0])

@php
    $items = [
        'dashboard' => ['grid', 'storefront.account'],
        'orders' => ['box', 'storefront.account.orders'],
        'codes' => ['key', 'storefront.account.codes'],
        'wallet' => ['wallet', 'storefront.account.wallet'],
        'wishlist' => ['heart', 'storefront.account.wishlist'],
        'reviews' => ['star', 'storefront.account.reviews'],
        'tickets' => ['chat', 'storefront.account.tickets'],
        'notifications' => ['bell', 'storefront.account.notifications'],
        'profile' => ['user', 'storefront.account.profile'],
        'security' => ['shield', 'storefront.account.security'],
    ];
@endphp

<nav class="-mx-4 shrink-0 md:-mx-8 lg:sticky lg:top-24 lg:mx-0 lg:w-[260px]" data-module="account-nav" aria-label="{{ __('account.nav.label') }}">
    <ul class="flex gap-2 overflow-x-auto px-4 pb-1 md:px-8 lg:flex-col lg:gap-1 lg:overflow-visible lg:rounded-xl lg:rounded-se-[28px] lg:border lg:border-line-subtle lg:bg-surface-raised lg:p-3">
        @foreach ($items as $key => [$icon, $route])
            @php($active = $current === $key)
            <li class="shrink-0">
                <a href="{{ url()->localized($route) }}" @if ($active) aria-current="page" @endif @class([
                    'relative flex h-10 items-center gap-2.5 rounded-md px-3 text-sm font-medium whitespace-nowrap transition duration-200 lg:h-11 lg:px-4',
                    'border border-accent-primary bg-surface-overlay text-content-primary lg:border-transparent lg:before:absolute lg:before:inset-y-2.5 lg:before:start-0 lg:before:w-[3px] lg:before:rounded-full lg:before:bg-accent-primary' => $active,
                    'border border-line-default bg-surface-raised text-content-secondary hover:text-content-primary lg:border-transparent lg:bg-transparent lg:hover:bg-surface-overlay' => ! $active,
                ])>
                    <x-store.icon :name="$icon" :size="18" @class(['text-accent-text' => $active]) />
                    <span>{{ __('account.nav.'.$key) }}</span>
                    @if ($key === 'notifications' && $unread > 0)
                        <span class="ms-auto inline-flex min-w-5 items-center justify-center rounded-full bg-accent-primary px-1.5 text-[11px] leading-5 font-semibold text-content-on-accent" dir="ltr">{{ $unread > 99 ? '99+' : $unread }}<span class="sr-only"> {{ __('account.notifications.unread_sr') }}</span></span>
                    @endif
                </a>
            </li>
        @endforeach
        <li class="shrink-0 lg:mt-2 lg:border-t lg:border-line-subtle lg:pt-2">
            <button type="button" class="flex h-10 w-full items-center gap-2.5 rounded-md border border-line-default px-3 text-sm font-medium whitespace-nowrap text-content-secondary hover:text-content-primary lg:h-11 lg:border-transparent lg:px-4 lg:hover:bg-surface-overlay" data-module="logout">
                <x-store.icon name="logout" :size="18" />
                <span>{{ __('account.nav.sign_out') }}</span>
            </button>
        </li>
    </ul>
</nav>
