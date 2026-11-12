@props(['storefront'])

{{-- Storefront footer, v2 information architecture: all products and the categories, the top
     brands, support and company pages. The v1 links (fixed category slugs, platforms) are gone. --}}
@php
    $navigation = $storefront['navigation'] ?? ['categories' => [], 'brands' => []];
    $shop = [__('storefront.catalog.all_products') => url()->localized('storefront.products.index')];

    foreach (array_slice($navigation['categories'], 0, 6) as $category) {
        $shop[$category['name']] = url()->localized('storefront.category', ['slug' => $category['slug']]);
    }

    $brands = [];

    foreach (array_slice($navigation['brands'], 0, 6) as $brand) {
        $brands[$brand['name']] = url()->localized('storefront.brand', ['slug' => $brand['slug']]);
    }

    $columns = array_filter([
        __('storefront.footer.shop') => $shop,
        __('storefront.catalog.top_brands') => $brands,
        __('storefront.footer.support') => [
            __('storefront.footer.contact') => url()->localized('storefront.pages.show', ['slug' => 'contact']),
            __('storefront.footer.refund_policy') => url()->localized('storefront.pages.show', ['slug' => 'refund-policy']),
            __('storefront.nav.account') => url()->localized('storefront.account'),
            __('storefront.search.title') => url()->localized('storefront.search'),
        ],
        __('storefront.footer.company') => [
            __('storefront.footer.about') => url()->localized('storefront.pages.show', ['slug' => 'about']),
            __('storefront.footer.terms') => url()->localized('storefront.pages.show', ['slug' => 'terms']),
            __('storefront.footer.privacy') => url()->localized('storefront.pages.show', ['slug' => 'privacy']),
        ],
    ]);
@endphp

<footer class="mt-16 border-t border-line-subtle bg-surface-base py-12 md:mt-24">
    <div class="container-page">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-[1.6fr_repeat(4,1fr)] grid-cols-2">
            <div class="col-span-2 flex flex-col gap-4 md:col-span-2 lg:col-span-1">
                <a href="{{ url()->localized('storefront.home') }}" class="self-start"><x-brand.logo :height="36" /></a>
                <p class="max-w-sm text-sm leading-6 text-content-secondary">{{ __('storefront.footer.tagline') }}</p>
            </div>
            @foreach ($columns as $heading => $links)
                <nav aria-label="{{ $heading }}">
                    <h2 class="mb-3 text-base font-semibold">{{ $heading }}</h2>
                    <ul class="flex flex-col gap-2.5">
                        @foreach ($links as $label => $href)
                            <li><a href="{{ $href }}" class="inline-flex min-h-6 text-sm text-content-secondary hover:text-content-primary">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach
        </div>
        <div class="mt-10 flex flex-col gap-4 border-t border-line-subtle pt-6 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap items-center gap-4">
                <p class="text-xs text-content-muted">© {{ now()->year }} {{ config('brand.name') }}</p>
                @if (app(\App\Domains\Analytics\Services\AnalyticsSettings::class)->browserTagEnabled())
                    <button type="button" class="text-xs text-content-secondary underline hover:text-content-primary" data-consent-open>{{ __('storefront.consent.settings') }}</button>
                @endif
            </div>
            <ul class="flex flex-wrap gap-2" aria-label="{{ __('storefront.footer.payments') }}">
                @foreach (['Cards', 'PayPal', 'Paymob'] as $method)
                    <li class="rounded-xs border border-line-default px-2 py-0.5 font-mono text-xs text-content-secondary">{{ $method }}</li>
                @endforeach
            </ul>
        </div>
        <p class="mt-4 text-xs leading-5 text-content-muted">{{ __('storefront.footer.disclaimer', ['name' => config('brand.name')]) }}</p>
    </div>
</footer>
