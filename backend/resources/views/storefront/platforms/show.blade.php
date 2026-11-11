@extends('layouts.storefront')

@php
    $art = ['01-volt', '02-ember', '03-ultra', '04-aqua', '05-flux', '06-sun'][((int) $index) % 6];
    $filtered = count(array_filter(request()->only(['category', 'delivery_type', 'min_price', 'max_price', 'sort'])));
    $page = $products->currentPage();
@endphp

{{-- Canonical and robots rules as the category page (SEO_STRATEGY.md section 2, M18). --}}
@section('title', $seo->title.($page > 1 ? ' — '.__('storefront.listing.page', ['page' => $page]) : ''))
@section('description', \Illuminate\Support\Str::limit((string) $seo->description, 155))
@section('robots', $seo->robots ?: ($filtered > 1 || $products->isEmpty() ? 'noindex,follow' : 'index,follow'))
@section('canonical', $seo->canonical ?: ($filtered === 0 && $page > 1 ? url()->current().'?page='.$page : url()->current()))

@section('content')
    <div class="container-page pt-6 md:pt-10">
        <x-store.breadcrumbs :items="[__('storefront.nav.home_short') => url()->localized('storefront.home'), __('storefront.platforms.title') => url()->localized('storefront.platforms.index'), $platform['name'] => null]" />

        <div class="keycut relative isolate mt-4 overflow-hidden border border-line-subtle bg-surface-raised">
            @if ($platform['banner'])
                <img src="{{ $platform['banner']['formats']['webp'] ?? $platform['banner']['url'] }}" alt="" class="absolute inset-0 -z-10 size-full object-cover" fetchpriority="high">
            @else
                <x-store.art :path="'platform/'.$art.'-1600x480'" width="1600" height="480" rtl eager class="absolute inset-0 -z-10 size-full object-cover object-right rtl:object-left" />
            @endif
            <div class="absolute inset-0 -z-10 bg-linear-to-r from-ink-950 via-ink-950/70 to-transparent rtl:bg-linear-to-l" aria-hidden="true"></div>
            <div class="flex min-h-40 flex-col justify-center gap-2 p-6 md:min-h-[200px] md:p-10">
                <p class="eyebrow text-content-secondary">{{ __('storefront.home.platform') }}</p>
                <h1 class="font-display text-[32px] leading-[38px] font-bold md:text-[40px] md:leading-[46px]">{{ $platform['name'] }}</h1>
                <p class="text-sm text-content-secondary">{{ trans_choice('storefront.listing.count', $products->total(), ['count' => $products->total()]) }}</p>
            </div>
        </div>

        @include('storefront.partials.listing', ['fixed' => 'platform', 'platforms' => []])

        @if ($platform['description'])
            <section class="prose-store mt-12 max-w-3xl">
                <h2>{{ __('storefront.listing.about', ['name' => $platform['name']]) }}</h2>
                <p>{{ $platform['description'] }}</p>
            </section>
        @endif
    </div>
@endsection
