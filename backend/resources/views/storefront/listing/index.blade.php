@extends('layouts.storefront')

{{-- All products (Storefront v2, Figma 110:852). Canonical and robots: SEO_STRATEGY.md section 2. --}}
@section('title', $seo->title.($page > 1 ? ' — '.__('storefront.listing.page', ['page' => $page]) : ''))
@section('description', \Illuminate\Support\Str::limit((string) $seo->description, 155))
@section('robots', $robots)
@section('canonical', $canonical)

@section('content')
    <div class="container-page pt-6 md:pt-8">
        <x-store.breadcrumbs :items="[__('storefront.nav.home_short') => url()->localized('storefront.home'), __('storefront.catalog.all_products') => null]" />
        <h1 class="mt-4 font-display text-[32px] leading-[38px] font-bold md:text-[40px] md:leading-[46px]">{{ __('storefront.catalog.all_products') }}</h1>
        <p class="mt-1 text-sm text-content-secondary">{{ __('storefront.catalog.summary', ['products' => trans_choice('storefront.catalog.products_label', $products->total(), ['count' => number_format($products->total())]), 'categories' => trans_choice('storefront.catalog.categories_count', $categoryCount, ['count' => $categoryCount])]) }}</p>

        @include('storefront.partials.listing')
    </div>
@endsection
