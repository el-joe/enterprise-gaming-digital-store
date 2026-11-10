@extends('layouts.storefront')

@php
    $banners = ['gift-cards', 'game-top-ups', 'subscriptions', 'gaming-wallets'];
    $filtered = count(array_filter(request()->only(['platform', 'delivery_type', 'min_price', 'max_price', 'sort']))) ;
    $page = $products->currentPage();
@endphp

@section('title', $category['name'].($page > 1 ? ' — '.__('storefront.listing.page', ['page' => $page]) : ''))
@section('description', $category['description'] ? \Illuminate\Support\Str::limit(strip_tags($category['description']), 155) : __('storefront.meta.category', ['name' => $category['name']]))
@section('robots', $filtered > 1 ? 'noindex,follow' : 'index,follow')
@section('canonical', $filtered > 0 ? url()->current() : ($page > 1 ? url()->current().'?page='.$page : url()->current()))

@section('content')
    <div class="container-page pt-6 md:pt-10">
        <x-store.breadcrumbs :items="[__('storefront.nav.home_short') => url()->localized('storefront.home'), $category['name'] => null]" />

        <div class="keycut relative isolate mt-4 overflow-hidden border border-line-subtle bg-surface-raised">
            @if ($category['banner'])
                <img src="{{ $category['banner']['formats']['webp'] ?? $category['banner']['url'] }}" alt="" class="absolute inset-0 -z-10 size-full object-cover" fetchpriority="high">
            @elseif (in_array($category['slug'], $banners, true))
                <x-store.art :path="'category/'.$category['slug'].'-1600x600'" width="1600" height="600" rtl eager class="absolute inset-0 -z-10 size-full object-cover object-right rtl:object-left" />
            @else
                <x-store.art path="platform/01-volt-1600x480" width="1600" height="480" rtl eager class="absolute inset-0 -z-10 size-full object-cover" />
            @endif
            <div class="absolute inset-0 -z-10 bg-linear-to-r from-ink-950 via-ink-950/70 to-transparent rtl:bg-linear-to-l" aria-hidden="true"></div>
            <div class="flex min-h-40 flex-col justify-center gap-2 p-6 md:min-h-52 md:p-10">
                <h1 class="font-display text-[32px] leading-[38px] font-bold md:text-[40px] md:leading-[46px]">{{ $category['name'] }}</h1>
                <p class="text-sm text-content-secondary">{{ trans_choice('storefront.listing.count', $products->total(), ['count' => $products->total()]) }}</p>
            </div>
        </div>

        @include('storefront.partials.listing', ['fixed' => 'category', 'categories' => []])

        @if ($category['description'])
            <section class="prose-store mt-12 max-w-3xl" aria-label="{{ __('storefront.listing.about', ['name' => $category['name']]) }}">
                {!! nl2br(e($category['description'])) !!}
            </section>
        @endif
    </div>
@endsection
