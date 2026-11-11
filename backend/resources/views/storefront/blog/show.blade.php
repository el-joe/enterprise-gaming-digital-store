@extends('layouts.storefront')

{{-- One blog post (M18): BlogPosting JSON-LD from BlogController, body sanitized on the server. --}}
@section('title', $seo->title)
@section('description', (string) $seo->description)
@section('robots', $seo->robots ?: 'index,follow')
@if ($seo->canonical)
    @section('canonical', $seo->canonical)
@endif

@push('head')
    <meta property="article:published_time" content="{{ $post['published_at']?->toIso8601String() }}">
    @if ($seo->image ?? $post['cover'])<meta property="og:image" content="{{ $seo->image ?? $post['cover']['url'] }}">@endif
@endpush

@section('content')
    <div class="container-page max-w-3xl pt-6 md:pt-10">
        <x-store.breadcrumbs :items="[__('storefront.nav.home_short') => url()->localized('storefront.home'), __('storefront.blog.title') => url()->localized('storefront.blog.index'), $post['title'] => null]" />
        <h1 class="mt-4 font-display text-[32px] leading-[38px] font-bold md:text-[40px] md:leading-[46px]">{{ $post['title'] }}</h1>
        @if ($post['published_at'])
            <p class="mt-2 text-xs text-content-muted">{{ __('storefront.blog.published', ['date' => $post['published_at']->translatedFormat('j F Y')]) }}</p>
        @endif
        @if ($post['cover'])
            <img src="{{ $post['cover']['formats']['webp'] ?? $post['cover']['url'] }}" alt="{{ $post['cover']['alt'] ?? '' }}" width="{{ $post['cover']['width'] ?? 1200 }}" height="{{ $post['cover']['height'] ?? 630 }}" fetchpriority="high" class="mt-6 aspect-[1200/630] w-full rounded-md object-cover">
        @endif
        <article class="prose-store mt-8">{!! $post['body'] !!}</article>
    </div>
@endsection
