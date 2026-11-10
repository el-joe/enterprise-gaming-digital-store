@extends('layouts.storefront')

@section('title', $term !== '' ? __('storefront.search.results_for', ['term' => $term]) : __('storefront.search.title'))
@section('robots', 'noindex,follow')

@section('content')
    <div class="container-page pt-6 md:pt-10">
        <h1 class="font-display text-[32px] leading-[38px] font-bold md:text-[40px] md:leading-[46px]">{{ __('storefront.search.title') }}</h1>

        <form action="{{ url()->current() }}" method="get" role="search" class="relative mt-6 max-w-3xl" data-module="search" data-search-page>
            <label for="search-page-input" class="sr-only">{{ __('storefront.search.label') }}</label>
            <x-store.icon name="search" :size="20" class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-accent-text" />
            <input id="search-page-input" name="q" type="search" value="{{ $term }}" maxlength="100" autocomplete="off" placeholder="{{ __('storefront.search.placeholder') }}"
                class="h-13 w-full rounded-md border border-ultra-400 bg-surface-sunken ps-12 pe-28 text-base placeholder:text-content-muted focus:outline-none"
                role="combobox" aria-expanded="false" aria-controls="search-page-panel" aria-autocomplete="list" data-search-input>
            <button type="submit" class="btn-primary absolute end-1.5 top-1/2 min-h-10 -translate-y-1/2">{{ __('storefront.search.submit') }}</button>
            <div id="search-page-panel" class="absolute inset-x-0 top-full z-30 mt-2 rounded-xl border border-line-default bg-surface-overlay p-4 shadow-overlay" data-search-panel hidden></div>
        </form>

        <div class="mt-6 flex flex-col gap-4 md:flex-row md:gap-10">
            <div data-recent-searches hidden>
                <p class="overline mb-2 text-content-muted">{{ __('storefront.search.recent') }}</p>
                <ul class="flex flex-wrap gap-2" data-recent-list></ul>
            </div>
            @if ($popular !== [])
                <div>
                    <p class="overline mb-2 text-content-muted">{{ __('storefront.search.popular') }}</p>
                    <ul class="flex flex-wrap gap-2">
                        @foreach ($popular as $popularTerm)
                            <li><a href="{{ url()->current().'?q='.urlencode($popularTerm) }}" class="chip min-h-8 hover:text-content-primary">{{ $popularTerm }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @if ($term !== '')
            @if ($links !== [])
                <section class="mt-10" aria-labelledby="search-links">
                    <h2 id="search-links" class="overline text-content-muted">{{ __('storefront.search.platforms_categories') }}</h2>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($links as $link)
                            <li>
                                <a href="{{ $link->type === 'gaming_platform' ? url()->localized('storefront.platforms.show', ['slug' => $link->slug]) : url()->localized('storefront.category', ['slug' => $link->slug]) }}"
                                    class="inline-flex min-h-10 items-center gap-2 rounded-md border border-line-default bg-surface-raised px-3 text-sm hover:border-line-strong">
                                    <x-store.icon name="{{ $link->type === 'gaming_platform' ? 'grid' : 'tag' }}" :size="16" class="text-content-muted" />
                                    {{ $link->title }}
                                    <span class="text-xs text-content-muted">{{ __('storefront.search.type.'.$link->type) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="mt-10" aria-labelledby="search-products">
                <h2 id="search-products" class="font-display text-2xl font-bold">{{ trans_choice('storefront.search.found', count($products), ['count' => count($products), 'term' => $term]) }}</h2>
                @if ($products === [])
                    <x-store.empty-state art="no-search-results" :title="__('storefront.search.empty_title')" :text="__('storefront.search.empty_text')">
                        <a href="{{ url()->localized('storefront.platforms.index') }}" class="btn-secondary mt-2">{{ __('storefront.search.browse') }}</a>
                    </x-store.empty-state>
                @else
                    <ul class="mt-6 grid grid-cols-1 gap-4 min-[420px]:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                        @foreach ($products as $product)
                            <li><x-store.product-card :product="$product" class="h-full" /></li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif
    </div>
@endsection
