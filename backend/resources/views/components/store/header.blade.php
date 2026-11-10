{{--
    Storefront header (Figma `Header`): logo, primary navigation, instant search, language and
    currency switcher, bag and account. `minimal` is the checkout header (logo + secure badge).
--}}
@props(['storefront', 'minimal' => false])

@php
    $nav = [
        ['label' => __('storefront.nav.gift_cards'), 'href' => url()->localized('storefront.category', ['slug' => 'gift-cards'])],
        ['label' => __('storefront.nav.top_ups'), 'href' => url()->localized('storefront.category', ['slug' => 'game-top-ups'])],
        ['label' => __('storefront.nav.subscriptions'), 'href' => url()->localized('storefront.category', ['slug' => 'subscriptions'])],
        ['label' => __('storefront.nav.platforms'), 'href' => url()->localized('storefront.platforms.index')],
    ];
    $currentLanguage = strtoupper($storefront['locale']);
@endphp

<header class="sticky top-0 z-40 border-b border-line-subtle bg-surface-base/95 backdrop-blur">
    <div class="container-page flex h-14 items-center gap-3 md:h-16 lg:h-[72px] lg:gap-6">
        @unless ($minimal)
            <button type="button" class="-ms-2 inline-flex size-10 items-center justify-center rounded-md text-content-secondary hover:text-content-primary lg:hidden" data-menu-toggle aria-controls="mobile-menu" aria-expanded="false">
                <x-store.icon name="menu" :label="__('storefront.nav.menu')" />
            </button>
        @endunless

        <a href="{{ url()->localized('storefront.home') }}" class="shrink-0" aria-label="{{ __('storefront.nav.home', ['name' => config('brand.name')]) }}">
            <span class="md:hidden"><x-brand.logo :height="28" eager /></span>
            <span class="hidden md:inline"><x-brand.logo :height="36" eager /></span>
        </a>

        @if ($minimal)
            <p class="ms-auto inline-flex items-center gap-2 text-sm text-status-success">
                <x-store.icon name="lock" :size="16" /> {{ __('storefront.checkout.secure') }}
            </p>
        @else
            <nav class="hidden items-center gap-5 lg:flex" aria-label="{{ __('storefront.nav.primary') }}">
                @foreach ($nav as $item)
                    <a href="{{ $item['href'] }}" class="text-sm font-medium text-content-secondary hover:text-content-primary">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <form action="{{ url()->localized('storefront.search') }}" method="get" role="search" class="relative ms-auto hidden min-w-0 flex-1 md:block lg:max-w-[434px]" data-module="search">
                <label for="site-search" class="sr-only">{{ __('storefront.search.label') }}</label>
                <x-store.icon name="search" :size="18" class="pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-content-muted" />
                <input id="site-search" name="q" type="search" autocomplete="off" maxlength="100" placeholder="{{ __('storefront.search.placeholder') }}"
                    class="h-10 w-full rounded-md border border-line-default bg-surface-raised ps-10 pe-10 text-sm placeholder:text-content-muted focus:border-focus-ring focus:outline-none"
                    role="combobox" aria-expanded="false" aria-controls="search-panel" aria-autocomplete="list" data-search-input>
                <kbd class="pointer-events-none absolute end-2.5 top-1/2 -translate-y-1/2 rounded-xs border border-line-default px-1.5 font-mono text-xs text-content-muted">/</kbd>
                <div id="search-panel" class="absolute inset-x-0 top-full z-50 mt-2 max-h-[70vh] overflow-y-auto rounded-xl border border-line-default bg-surface-overlay p-4 shadow-overlay" data-search-panel hidden></div>
            </form>

            <div class="ms-auto flex items-center gap-1 md:ms-0 lg:gap-2">
                <a href="{{ url()->localized('storefront.search') }}" class="inline-flex size-10 items-center justify-center rounded-md text-content-secondary hover:text-content-primary md:hidden">
                    <x-store.icon name="search" :label="__('storefront.search.label')" />
                </a>

                <div class="relative hidden lg:block" data-module="popover">
                    <button type="button" class="inline-flex h-10 items-center gap-2 rounded-md px-2 text-sm font-medium text-content-secondary hover:text-content-primary" data-popover-toggle aria-expanded="false" aria-controls="region-popover">
                        <x-store.icon name="globe" :size="18" />
                        <span>{{ $currentLanguage }} · {{ $storefront['currency'] }}</span>
                        <span class="sr-only">{{ __('storefront.nav.language_currency') }}</span>
                    </button>
                    <div id="region-popover" class="absolute end-0 top-full z-50 mt-2 w-64 rounded-xl border border-line-default bg-surface-overlay p-4 shadow-overlay" data-popover-panel hidden>
                        <x-store.region-switcher :storefront="$storefront" id-prefix="desktop" />
                    </div>
                </div>

                <a href="{{ url()->localized('storefront.cart') }}" class="relative inline-flex size-10 items-center justify-center rounded-md text-content-secondary hover:text-content-primary">
                    <x-store.icon name="bag" :label="__('storefront.nav.bag_count', ['count' => $storefront['bag_count']])" />
                    <span @class(['absolute end-0.5 top-0.5 min-w-4 rounded-full bg-accent-primary px-1 text-center text-[11px] leading-4 font-semibold text-content-on-accent', 'hidden' => $storefront['bag_count'] === 0]) data-bag-count aria-hidden="true">{{ $storefront['bag_count'] }}</span>
                </a>

                @if ($storefront['user'])
                    <a href="{{ url()->localized('storefront.account') }}" class="inline-flex h-10 items-center gap-2 rounded-md px-2 text-sm font-medium text-content-secondary hover:text-content-primary">
                        <x-store.icon name="user" :size="18" />
                        <span class="hidden max-w-32 truncate lg:inline">{{ $storefront['user']['name'] }}</span>
                        <span class="sr-only lg:hidden">{{ __('storefront.nav.account') }}</span>
                    </a>
                @else
                    <a href="{{ url()->localized('storefront.login') }}" class="keycut-sm hidden h-8 items-center bg-accent-primary px-3 text-sm font-semibold text-content-on-accent hover:bg-accent-hover sm:inline-flex">{{ __('storefront.nav.sign_in') }}</a>
                    <a href="{{ url()->localized('storefront.login') }}" class="inline-flex size-10 items-center justify-center rounded-md text-content-secondary sm:hidden">
                        <x-store.icon name="user" :label="__('storefront.nav.sign_in')" />
                    </a>
                @endif
            </div>
        @endif
    </div>

    @unless ($minimal)
        <div id="mobile-menu" class="border-t border-line-subtle bg-surface-base lg:hidden" data-menu hidden>
            <nav class="container-page flex flex-col py-3" aria-label="{{ __('storefront.nav.primary') }}">
                @foreach ($nav as $item)
                    <a href="{{ $item['href'] }}" class="flex min-h-11 items-center border-b border-line-subtle text-base font-medium">{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <div class="container-page pb-4">
                <x-store.region-switcher :storefront="$storefront" id-prefix="mobile" />
            </div>
        </div>
    @endunless
</header>
