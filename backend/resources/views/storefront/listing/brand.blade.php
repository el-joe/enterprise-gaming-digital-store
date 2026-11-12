@extends('layouts.storefront')

{{--
    Brand page `/b/{slug}` (Storefront v2, Figma 112:6798): header with the brand logo (supplier
    synced or staff) or the logo slot, name and intro; the listing without the Category and Brand
    facets. Canonical and robots as the category page (SEO_STRATEGY.md section 2, M18).
--}}
@section('title', $seo->title.($page > 1 ? ' — '.__('storefront.listing.page', ['page' => $page]) : ''))
@section('description', \Illuminate\Support\Str::limit((string) $seo->description, 155))
@section('robots', $seo->robots ?: $robots)
@section('canonical', $seo->canonical ?: $canonical)

@section('content')
    <div class="container-page pt-6 md:pt-8">
        <x-store.breadcrumbs :items="[__('storefront.nav.home_short') => url()->localized('storefront.home'), __('storefront.catalog.all_products') => url()->localized('storefront.products.index'), $brand['name'] => null]" />

        <section class="keycut mt-4 flex flex-col gap-5 border border-line-subtle bg-surface-raised p-5 sm:flex-row sm:items-center md:p-6">
            <x-store.tile :slug="$brand['slug']" :logo="$brand['logo']" :alt="__('storefront.catalog.logo', ['name' => $brand['name']])" eager class="aspect-[8/5] w-44 shrink-0 md:w-52" data-brand-logo />
            <div class="flex min-w-0 flex-col gap-2">
                <p class="eyebrow text-accent-text">{{ __('storefront.catalog.brand_eyebrow') }}</p>
                <h1 class="font-display text-[32px] leading-[38px] font-bold md:text-[40px] md:leading-[46px]">{{ $brand['name'] }}</h1>
                @if ($brand['description'])
                    <p class="line-clamp-3 max-w-2xl text-sm leading-6 text-content-secondary">{{ $brand['description'] }}</p>
                @endif
                <p class="flex flex-wrap gap-2 text-xs">
                    <span class="chip">{{ trans_choice('storefront.catalog.products_label', $products->total(), ['count' => number_format($products->total())]) }}</span>
                </p>
            </div>
        </section>

        @include('storefront.partials.listing')
    </div>
@endsection
