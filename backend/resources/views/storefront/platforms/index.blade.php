@extends('layouts.storefront')

@section('title', __('storefront.platforms.title'))
@section('description', __('storefront.platforms.description'))

@section('content')
    <div class="container-page pt-6 md:pt-10">
        <x-store.breadcrumbs :items="[__('storefront.nav.home_short') => url()->localized('storefront.home'), __('storefront.platforms.title') => null]" />
        <h1 class="mt-4 font-display text-[32px] leading-[38px] font-bold md:text-[40px] md:leading-[46px]">{{ __('storefront.platforms.title') }}</h1>
        <p class="mt-2 max-w-2xl text-sm text-content-secondary">{{ __('storefront.platforms.intro') }}</p>

        @if ($platforms === [])
            <x-store.empty-state art="no-search-results" :title="__('storefront.platforms.empty')" />
        @else
            <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                @foreach ($platforms as $platform)
                    <li class="card group relative flex flex-col overflow-hidden transition duration-200 hover:-translate-y-0.5 hover:border-accent-primary/60">
                        <x-store.tile :slug="$platform['slug']" :overline="__('storefront.home.platform')" :name="$platform['name']" :image="$platform['banner'] ?? $platform['logo']" class="h-36 p-4" />
                        <div class="flex flex-1 flex-col gap-3 p-4">
                            @if ($platform['description'])
                                <p class="line-clamp-2 text-sm text-content-secondary">{{ $platform['description'] }}</p>
                            @endif
                            <div class="mt-auto flex items-center justify-between text-sm">
                                <span class="text-content-muted">{{ trans_choice('storefront.listing.count', $platform['product_count'], ['count' => $platform['product_count']]) }}</span>
                                <a href="{{ url()->localized('storefront.platforms.show', ['slug' => $platform['slug']]) }}" class="inline-flex items-center gap-1 font-medium text-accent-text after:absolute after:inset-0">
                                    {{ __('storefront.platforms.shop', ['name' => $platform['name']]) }} <x-store.icon name="arrow" :size="16" />
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
