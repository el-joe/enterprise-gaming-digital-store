{{--
    Storefront header v2 (Figma "Store Header v2" 107:256, "Header v2" 109:133-109:319): logo, the
    "All categories" mega menu (categories with counts, top brands per category, popular filters),
    search, language and currency, wishlist, bag and account; a second row with up to 8 featured
    categories (admin "Featured in header"). The v1 links (Gift Cards, Top-ups, Subscriptions,
    Platforms) are gone. On mobile the bar keeps menu, logo, account, wishlist and bag (48 px
    each); the menu opens a categories sheet with the language and currency switches. Behaviour:
    resources/js/modules/mega-menu.js. `minimal` is the checkout header (logo + secure badge).
--}}
@props(['storefront', 'minimal' => false])

@php
    $navigation = $storefront['navigation'] ?? ['total' => 0, 'categories' => [], 'featured' => [], 'brands' => []];
    $currentLanguage = strtoupper($storefront['locale']);
    $allProductsUrl = url()->localized('storefront.products.index');
    $wishlistUrl = $storefront['user'] ? url()->localized('storefront.account.wishlist') : url()->localized('storefront.login');
    $accountUrl = $storefront['user'] ? url()->localized('storefront.account') : url()->localized('storefront.login');
    $icons = ['gam' => 'gamepad', 'shop' => 'tag', 'entertain' => 'play', 'stream' => 'play', 'pay' => 'card', 'crypto' => 'wallet', 'travel' => 'globe', 'soft' => 'key', 'mobile' => 'phone', 'top' => 'zap'];
    $iconFor = static function (string $slug) use ($icons): string {
        foreach ($icons as $needle => $icon) {
            if (str_contains($slug, $needle)) {
                return $icon;
            }
        }

        return 'box';
    };
    $chipFilters = static fn (array $category): array => array_map(static fn (string $type): array => [
        'label' => __('storefront.delivery.'.$type),
        'href' => url()->localized('storefront.category', ['slug' => $category['slug']]).'?delivery_type='.$type,
    ], $category['delivery_types']);
@endphp

<header class="sticky top-0 z-40 border-b border-line-subtle bg-surface-base/95 backdrop-blur" @unless ($minimal) data-module="mega-menu" @endunless>
    <div class="container-page relative flex h-14 items-center gap-1 md:h-16 lg:h-[72px] lg:gap-4">
        @unless ($minimal)
            <button type="button" class="-ms-2 inline-flex size-12 items-center justify-center rounded-md text-content-secondary hover:text-content-primary lg:hidden" data-sheet-open aria-controls="mobile-menu" aria-expanded="false" aria-haspopup="dialog">
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
            <button type="button" class="keycut-btn hidden h-11 shrink-0 items-center gap-2 border border-line-default bg-surface-raised px-4 text-sm font-medium hover:border-line-strong aria-expanded:border-accent-primary lg:inline-flex"
                data-mega-toggle aria-expanded="false" aria-controls="mega-menu">
                <x-store.icon name="grid" :size="18" class="text-accent-text" />
                {{ __('storefront.catalog.all_categories') }}
                <x-store.icon name="chevron-down" :size="16" class="transition" />
            </button>

            <form action="{{ url()->localized('storefront.search') }}" method="get" role="search" class="relative hidden min-w-0 flex-1 md:block" data-module="search">
                <label for="site-search" class="sr-only">{{ __('storefront.search.label') }}</label>
                <x-store.icon name="search" :size="18" class="pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-content-muted" />
                <input id="site-search" name="q" type="search" autocomplete="off" maxlength="100" placeholder="{{ __('storefront.search.placeholder') }}"
                    class="h-12 w-full rounded-md border border-line-default bg-surface-sunken ps-10 pe-10 text-sm placeholder:text-content-muted focus:border-focus-ring focus:outline-none"
                    role="combobox" aria-expanded="false" aria-controls="search-panel" aria-autocomplete="list" data-search-input>
                <kbd class="pointer-events-none absolute end-2.5 top-1/2 -translate-y-1/2 rounded-xs border border-line-strong px-1.5 font-mono text-xs text-content-muted">/</kbd>
                <div id="search-panel" class="absolute inset-x-0 top-full z-50 mt-2 max-h-[70vh] overflow-y-auto rounded-xl border border-line-default bg-surface-overlay p-4 shadow-overlay" data-search-panel hidden></div>
            </form>

            <div class="ms-auto flex items-center gap-0.5 md:ms-0 lg:gap-1">
                <div class="relative hidden lg:block" data-module="popover">
                    <button type="button" class="inline-flex h-11 items-center gap-1.5 rounded-md px-3 text-xs font-medium hover:bg-surface-raised" data-popover-toggle aria-expanded="false" aria-controls="region-popover">
                        <x-store.icon name="globe" :size="18" />
                        <span>{{ $currentLanguage }} · {{ $storefront['currency'] }}</span>
                        <x-store.icon name="chevron-down" :size="14" />
                        <span class="sr-only">{{ __('storefront.nav.language_currency') }}</span>
                    </button>
                    <div id="region-popover" class="absolute end-0 top-full z-50 mt-2 w-64 rounded-xl border border-line-default bg-surface-overlay p-4 shadow-overlay" data-popover-panel hidden>
                        <x-store.region-switcher :storefront="$storefront" id-prefix="desktop" />
                    </div>
                </div>

                <a href="{{ $accountUrl }}" class="inline-flex size-12 items-center justify-center rounded-md text-content-secondary hover:text-content-primary lg:hidden">
                    <x-store.icon name="user" :label="$storefront['user'] ? __('storefront.nav.account') : __('storefront.nav.sign_in')" />
                </a>

                <a href="{{ $wishlistUrl }}" class="inline-flex size-12 items-center justify-center rounded-md text-content-secondary hover:text-content-primary lg:size-11">
                    <x-store.icon name="heart" :label="__('storefront.catalog.wishlist')" />
                </a>

                <a href="{{ url()->localized('storefront.cart') }}" class="relative inline-flex size-12 items-center justify-center rounded-md text-content-secondary hover:text-content-primary lg:size-11">
                    <x-store.icon name="bag" :label="__('storefront.nav.bag_count', ['count' => $storefront['bag_count']])" />
                    <span @class(['absolute end-1 top-1 min-w-[18px] rounded-full bg-accent-primary px-1 text-center text-xs leading-[18px] font-medium text-content-on-accent', 'hidden' => $storefront['bag_count'] === 0]) data-bag-count aria-hidden="true">{{ $storefront['bag_count'] }}</span>
                </a>

                <a href="{{ $accountUrl }}" class="ms-1 hidden h-11 items-center gap-2 rounded-md border border-line-default px-3.5 text-sm font-medium hover:border-line-strong lg:inline-flex">
                    <x-store.icon name="user" :size="18" />
                    <span class="max-w-32 truncate">{{ $storefront['user']['name'] ?? __('storefront.catalog.account') }}</span>
                </a>
            </div>

            {{-- Desktop mega menu (Figma 109:371): 1000 px panel, 320 px category list. --}}
            <div id="mega-menu" class="absolute start-4 top-full z-50 mt-2 grid w-[min(1000px,calc(100vw-2rem))] grid-cols-[320px_1fr] overflow-hidden rounded-xl border border-line-default bg-surface-overlay shadow-overlay lg:start-20" data-mega-panel hidden>
                <nav class="border-e border-line-subtle p-4" aria-label="{{ __('storefront.catalog.shop_by_category') }}">
                    <p class="eyebrow mb-3 px-3 text-content-muted">{{ __('storefront.catalog.shop_by_category') }}</p>
                    <ul class="flex flex-col gap-1" data-mega-list>
                        <li>
                            <a href="{{ $allProductsUrl }}" class="flex min-h-12 items-center gap-3 rounded-md border-s-2 border-transparent px-3 text-sm font-medium hover:bg-surface-raised data-[active]:border-accent-primary data-[active]:bg-surface-raised data-[active]:text-accent-text" data-mega-item="all">
                                <x-store.icon name="grid" :size="18" />
                                <span class="flex-1">{{ __('storefront.catalog.all_products') }}</span>
                                <span class="font-mono text-xs text-content-muted" dir="ltr">{{ number_format($navigation['total']) }}</span>
                                <x-store.icon name="chevron-forward" :size="16" class="text-content-muted" />
                            </a>
                        </li>
                        @foreach ($navigation['categories'] as $category)
                            <li>
                                <a href="{{ url()->localized('storefront.category', ['slug' => $category['slug']]) }}" class="flex min-h-12 items-center gap-3 rounded-md border-s-2 border-transparent px-3 text-sm font-medium hover:bg-surface-raised data-[active]:border-accent-primary data-[active]:bg-surface-raised data-[active]:text-accent-text" data-mega-item="{{ $category['slug'] }}">
                                    <x-store.icon :name="$iconFor($category['slug'])" :size="18" />
                                    <span class="flex-1 truncate">{{ $category['name'] }}</span>
                                    <span class="font-mono text-xs text-content-muted" dir="ltr">{{ number_format($category['count']) }}</span>
                                    <x-store.icon name="chevron-forward" :size="16" class="text-content-muted" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
                <div class="p-6">
                    <section data-mega-section="all" aria-label="{{ __('storefront.catalog.top_brands') }}">
                        @include('storefront.partials.mega-brands', ['title' => __('storefront.catalog.top_brands'), 'meta' => trans_choice('storefront.catalog.products_label', $navigation['total'], ['count' => number_format($navigation['total'])]), 'href' => $allProductsUrl, 'action' => __('storefront.catalog.see_all_in', ['name' => __('storefront.catalog.all_products')]), 'brands' => array_slice($navigation['brands'], 0, 8), 'filters' => [['label' => __('storefront.catalog.in_stock'), 'href' => $allProductsUrl.'?in_stock=1'], ['label' => __('storefront.catalog.on_sale'), 'href' => $allProductsUrl.'?on_sale=1']]])
                    </section>
                    @foreach ($navigation['categories'] as $category)
                        <section data-mega-section="{{ $category['slug'] }}" aria-label="{{ __('storefront.catalog.top_brands_in', ['name' => $category['name']]) }}" hidden>
                            @include('storefront.partials.mega-brands', [
                                'title' => __('storefront.catalog.top_brands_in', ['name' => $category['name']]),
                                'meta' => trans_choice('storefront.catalog.products_label', $category['count'], ['count' => number_format($category['count'])]).' · '.trans_choice('storefront.catalog.brands_count', $category['brand_count'], ['count' => $category['brand_count']]),
                                'href' => url()->localized('storefront.category', ['slug' => $category['slug']]),
                                'action' => __('storefront.catalog.see_all_in', ['name' => $category['name']]),
                                'brands' => $category['brands'],
                                'filters' => [...$chipFilters($category), ['label' => __('storefront.catalog.in_stock'), 'href' => url()->localized('storefront.category', ['slug' => $category['slug']]).'?in_stock=1']],
                            ])
                        </section>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @unless ($minimal)
        <div class="container-page pb-3 md:hidden">
            <a href="{{ url()->localized('storefront.search') }}" class="flex h-12 items-center gap-3 rounded-md border border-line-default bg-surface-sunken px-4 text-sm text-content-muted">
                <x-store.icon name="search" :size="18" /> {{ __('storefront.search.placeholder_short') }}
            </a>
        </div>

        @if ($navigation['featured'] !== [])
            <nav class="border-t border-line-subtle" aria-label="{{ __('storefront.catalog.featured_categories') }}">
                <ul class="container-page flex h-[60px] items-center gap-2 overflow-x-auto lg:h-[52px]">
                    <li class="hidden shrink-0 text-xs font-medium text-content-muted lg:block" aria-hidden="true">{{ __('storefront.catalog.featured') }}</li>
                    @foreach ($navigation['featured'] as $featured)
                        @php $featuredUrl = url()->localized('storefront.category', ['slug' => $featured['slug']]); @endphp
                        <li class="shrink-0">
                            <a href="{{ $featuredUrl }}" @class(['inline-flex h-12 items-center rounded-full border px-4 text-sm font-medium lg:h-9', 'border-accent-primary bg-accent-tint text-accent-text' => url()->current() === $featuredUrl, 'border-line-default bg-surface-raised hover:border-line-strong' => url()->current() !== $featuredUrl]) data-featured-category @if (url()->current() === $featuredUrl) aria-current="page" @endif>{{ $featured['name'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        {{-- Mobile categories sheet (Figma 109:803): accordion per category, language and currency. --}}
        <div id="mobile-menu" class="fixed inset-0 z-50 flex flex-col bg-surface-base lg:hidden" role="dialog" aria-modal="true" aria-labelledby="mobile-menu-title" data-sheet hidden>
            <div class="flex h-14 shrink-0 items-center justify-between border-b border-line-subtle px-4">
                <h2 id="mobile-menu-title" class="text-lg font-semibold">{{ __('storefront.catalog.menu_title') }}</h2>
                <button type="button" class="-me-2 inline-flex size-12 items-center justify-center rounded-md" data-sheet-close>
                    <x-store.icon name="x" :label="__('storefront.catalog.close_menu')" />
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-4 py-4">
                <label for="menu-filter" class="sr-only">{{ __('storefront.catalog.search_menu') }}</label>
                <div class="relative">
                    <x-store.icon name="search" :size="18" class="pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-content-muted" />
                    <input id="menu-filter" type="search" autocomplete="off" class="h-12 w-full rounded-md border border-line-default bg-surface-sunken ps-10 pe-3 text-sm placeholder:text-content-muted focus:border-focus-ring focus:outline-none" placeholder="{{ __('storefront.catalog.search_menu') }}" data-sheet-filter>
                </div>
                <ul class="mt-4 flex flex-col gap-1">
                    <li>
                        <a href="{{ $allProductsUrl }}" class="flex min-h-12 items-center gap-3 rounded-md px-2 text-base font-medium">
                            <x-store.icon name="grid" :size="20" />
                            <span class="flex-1">{{ __('storefront.catalog.all_products') }}</span>
                            <span class="font-mono text-xs text-content-muted" dir="ltr">{{ number_format($navigation['total']) }}</span>
                            <x-store.icon name="chevron-forward" :size="16" class="text-content-muted" />
                        </a>
                    </li>
                    @foreach ($navigation['categories'] as $category)
                        @php $panelId = 'sheet-'.$category['slug']; @endphp
                        <li data-sheet-entry data-terms="{{ mb_strtolower($category['name'].' '.implode(' ', array_column($category['brands'], 'name'))) }}">
                            <button type="button" class="flex min-h-12 w-full items-center gap-3 rounded-md px-2 text-start text-base font-medium aria-expanded:bg-surface-raised aria-expanded:text-accent-text" aria-expanded="false" aria-controls="{{ $panelId }}" data-accordion-toggle>
                                <x-store.icon :name="$iconFor($category['slug'])" :size="20" />
                                <span class="flex-1">{{ $category['name'] }}</span>
                                <span class="font-mono text-xs text-content-muted" dir="ltr">{{ number_format($category['count']) }}</span>
                                <x-store.icon name="chevron-down" :size="16" class="text-content-muted" />
                            </button>
                            <div id="{{ $panelId }}" class="px-2 pt-2 pb-3" hidden>
                                @if ($category['brands'] !== [])
                                    <ul class="grid grid-cols-3 gap-2">
                                        @foreach (array_slice($category['brands'], 0, 6) as $brand)
                                            <li>
                                                <a href="{{ url()->localized('storefront.brand', ['slug' => $brand['slug']]) }}" class="block">
                                                    <x-store.tile :slug="$brand['slug']" :logo="$brand['logo']" :alt="__('storefront.catalog.logo', ['name' => $brand['name']])" class="aspect-[8/5] w-full" />
                                                    <span class="mt-1 block truncate text-center text-xs font-medium">{{ $brand['name'] }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <a href="{{ url()->localized('storefront.category', ['slug' => $category['slug']]) }}" class="btn-primary mt-3 w-full">{{ __('storefront.catalog.see_all_in_count', ['name' => $category['name'], 'count' => number_format($category['count'])]) }} <x-store.icon name="arrow" :size="16" /></a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="shrink-0 border-t border-line-subtle px-4">
                <button type="button" class="flex min-h-14 w-full items-center gap-3 text-start text-base font-medium" aria-expanded="false" aria-controls="sheet-region" data-accordion-toggle>
                    <x-store.icon name="globe" :size="20" />
                    <span class="flex-1">{{ __('storefront.catalog.language_currency') }}</span>
                    <span class="font-mono text-xs text-content-muted">{{ $currentLanguage }} · {{ $storefront['currency'] }}</span>
                    <x-store.icon name="chevron-down" :size="16" class="text-content-muted" />
                </button>
                <div id="sheet-region" class="pb-4" hidden>
                    <x-store.region-switcher :storefront="$storefront" id-prefix="mobile" />
                </div>
            </div>
        </div>
    @endunless
</header>
