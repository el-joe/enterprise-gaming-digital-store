@extends('layouts.storefront')

@php
    $regionNames = collect([...$product['regions'], ...$product['variants_summary']['regions']])->pluck('name', 'code')->all();
    $regions = collect($variants)->pluck('region')->unique()->values()->all();
    $selected = collect($variants)->firstWhere('purchasable', true) ?? ($variants[0] ?? null);
    $selectedRegion = $selected['region'] ?? null;
    $instant = $product['delivery_type'] === 'instant_code';
    $paymentLabels = ['stripe' => __('storefront.payment.card'), 'paypal' => 'PayPal', 'paymob' => 'Paymob', 'wallet' => __('storefront.payment.wallet')];
    $availability = $product['availability'] ?? 'out_of_stock';
@endphp

@section('title', $product['seo']['title'])
@section('description', \Illuminate\Support\Str::limit((string) ($product['seo']['description'] ?? $product['short_description']), 155))
@section('robots', $product['seo']['robots'] ?: 'index,follow')

@push('head')
    @if ($product['seo']['image'])<meta property="og:image" content="{{ $product['seo']['image'] }}">@endif
@endpush

@section('content')
    <div class="container-page pt-6 pb-24 md:pt-10 lg:pb-0">
        <x-store.breadcrumbs :items="array_filter([
            __('storefront.nav.home_short') => url()->localized('storefront.home'),
            ($product['category']['name'] ?? '') => $product['category']['slug'] ? url()->localized('storefront.category', ['slug' => $product['category']['slug']]) : null,
            $product['name'] => null,
        ], static fn ($value, $key) => $key !== '', ARRAY_FILTER_USE_BOTH)" />

        <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_480px] lg:gap-x-12">
            {{-- Gallery (first on every width) --}}
            <div class="min-w-0">
                <x-store.tile :slug="$product['platform']['slug'] ?? $product['slug']" :overline="$product['category']['name']" :name="$product['platform']['name']"
                    :image="$product['images'][0] ?? null" large eager class="aspect-[16/9] p-5 md:aspect-[752/340] md:p-6" />

                @if (count($product['images']) > 1)
                    <ul class="mt-2.5 grid grid-cols-3 gap-2" aria-label="{{ __('storefront.product.gallery') }}">
                        @foreach (array_slice($product['images'], 1, 3) as $image)
                            <li><img src="{{ $image['formats']['webp'] ?? $image['url'] }}" alt="{{ $image['alt'] ?? '' }}" @if ($image['width']) width="{{ $image['width'] }}" height="{{ $image['height'] }}" @endif class="keycut aspect-[3/1] w-full object-cover" loading="lazy" decoding="async"></li>
                        @endforeach
                    </ul>
                @endif

            </div>

            {{-- Buy box: second on mobile, beside the gallery and content on desktop --}}
            @include('storefront.partials.buy-box')

            {{-- Description, instructions, terms: after the buy box on mobile --}}
            <div class="min-w-0 lg:col-start-1 lg:row-start-2">
                <div data-module="tabs">
                    <div class="flex gap-1 overflow-x-auto border-b border-line-default" role="tablist" aria-label="{{ __('storefront.product.details') }}">
                        @foreach (['description' => true, 'instructions' => (bool) $product['instructions'], 'terms' => (bool) $product['terms']] as $tab => $shown)
                            @continue(! $shown)
                            <button type="button" role="tab" id="tab-{{ $tab }}" aria-controls="panel-{{ $tab }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" @unless ($loop->first) tabindex="-1" @endunless
                                class="-mb-px border-b-2 border-transparent px-4 py-3 text-sm font-medium whitespace-nowrap text-content-secondary aria-selected:border-accent-primary aria-selected:text-content-primary">
                                {{ __('storefront.product.tab.'.$tab) }}
                            </button>
                        @endforeach
                    </div>
                    <div id="panel-description" role="tabpanel" aria-labelledby="tab-description" class="prose-store pt-5">
                        @if ($product['description'])
                            {!! $product['description'] !!}
                        @else
                            <p>{{ $product['short_description'] }}</p>
                        @endif
                    </div>
                    @if ($product['instructions'])
                        <div id="panel-instructions" role="tabpanel" aria-labelledby="tab-instructions" class="pt-5" hidden>
                            <ol class="flex flex-col gap-3">
                                @foreach (array_values(array_filter(array_map('trim', preg_split('/\R/', $product['instructions'])))) as $step)
                                    <li class="flex items-start gap-3 text-sm leading-6">
                                        <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-accent-tint text-xs font-semibold text-accent-text">{{ $loop->iteration }}</span>
                                        <span>{{ $step }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                    @if ($product['terms'])
                        <div id="panel-terms" role="tabpanel" aria-labelledby="tab-terms" class="pt-5 text-sm leading-6 whitespace-pre-line text-content-secondary" hidden>{{ $product['terms'] }}</div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Reviews --}}
        <section id="reviews" class="pt-14 md:pt-[72px]" aria-labelledby="reviews-title">
            <x-store.section-head :title="__('storefront.reviews.title')" id="reviews-title" />
            @if ($reviews === [])
                <x-store.empty-state art="no-reviews" :title="__('storefront.reviews.empty')" :text="__('storefront.reviews.empty_text')" class="py-8" />
            @else
                <ul class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($reviews as $review)
                        <li class="card flex flex-col gap-3 p-5">
                            <div class="flex items-center justify-between gap-2">
                                <x-store.stars :rating="$review['rating']" />
                                @if ($review['verified_purchase'])<span class="chip">{{ __('storefront.reviews.verified') }}</span>@endif
                            </div>
                            @if ($review['title'])<p class="font-semibold">{{ $review['title'] }}</p>@endif
                            @if ($review['body'])<p class="text-sm leading-6 text-content-secondary">{{ $review['body'] }}</p>@endif
                            <p class="mt-auto text-sm">{{ $review['reviewer'] }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        @if ($related !== [])
            <section class="pt-14 md:pt-[72px]" aria-labelledby="related-title">
                <x-store.section-head :title="__('storefront.product.related')" id="related-title"
                    :href="$product['category']['slug'] ? url()->localized('storefront.category', ['slug' => $product['category']['slug']]) : null" />
                <ul class="mt-6 grid grid-cols-1 gap-4 min-[420px]:grid-cols-2 lg:grid-cols-4 lg:gap-6">
                    @foreach ($related as $card)
                        <li><x-store.product-card :product="$card" class="h-full" /></li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>

    {{-- Sticky buy bar (mobile and tablet) --}}
    @if ($variants !== [])
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-line-default bg-surface-base/95 py-3 backdrop-blur lg:hidden">
            <div class="container-page flex items-center justify-between gap-3">
                <p class="font-display text-2xl font-bold" dir="ltr" data-variant-price>{{ ($selected['purchasable'] ?? false) ? $selected['price']['formatted'] : '—' }}</p>
                <button type="button" class="btn-primary flex-1 sm:flex-none" data-sticky-buy @disabled(! ($selected['purchasable'] ?? false))>{{ __('storefront.product.buy_now') }}</button>
            </div>
        </div>
    @endif
@endsection
