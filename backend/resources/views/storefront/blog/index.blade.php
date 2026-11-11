@extends('layouts.storefront')

{{-- Blog index (M18). Paginated pages are self-canonical with "Page N" in the title; an empty blog is noindex. --}}
@php($page = $posts->currentPage())

@section('title', __('storefront.blog.title').($page > 1 ? ' — '.__('storefront.blog.page', ['page' => $page]) : ''))
@section('description', __('storefront.blog.meta', ['site' => config('brand.name')]))
@section('robots', $posts->isEmpty() ? 'noindex,follow' : 'index,follow')
@section('canonical', $page > 1 ? url()->current().'?page='.$page : url()->current())

@section('content')
    <div class="container-page pt-6 md:pt-10">
        <x-store.breadcrumbs :items="[__('storefront.nav.home_short') => url()->localized('storefront.home'), __('storefront.blog.title') => null]" />
        <h1 class="mt-4 font-display text-[32px] leading-[38px] font-bold md:text-[40px] md:leading-[46px]">{{ __('storefront.blog.title') }}</h1>
        <p class="mt-2 max-w-2xl text-sm text-content-secondary">{{ __('storefront.blog.intro') }}</p>

        @if ($posts->isEmpty())
            <x-store.empty-state art="no-search-results" :title="__('storefront.blog.empty')" :text="__('storefront.blog.empty_text')" />
        @else
            <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                @foreach ($posts as $post)
                    <li class="card group relative flex flex-col overflow-hidden">
                        @if ($post['cover'])
                            <img src="{{ $post['cover']['formats']['webp'] ?? $post['cover']['url'] }}" alt="" width="{{ $post['cover']['width'] ?? 1200 }}" height="{{ $post['cover']['height'] ?? 630 }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" @if ($loop->first) fetchpriority="high" @endif class="aspect-[1200/630] w-full object-cover">
                        @endif
                        <div class="flex flex-1 flex-col gap-2 p-4">
                            @if ($post['published_at'])
                                <p class="text-xs text-content-muted">{{ __('storefront.blog.published', ['date' => $post['published_at']->translatedFormat('j F Y')]) }}</p>
                            @endif
                            <h2 class="text-lg font-semibold">
                                <a href="{{ url()->localized('storefront.blog.show', ['slug' => $post['slug']]) }}" class="after:absolute after:inset-0">{{ $post['title'] }}</a>
                            </h2>
                            @if ($post['excerpt'])<p class="line-clamp-3 text-sm text-content-secondary">{{ $post['excerpt'] }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-8">@include('storefront.partials.pagination', ['paginator' => $posts])</div>
        @endif
    </div>
@endsection
