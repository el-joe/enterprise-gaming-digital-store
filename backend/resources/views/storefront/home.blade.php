@extends('layouts.storefront')

@section('description', __('storefront.meta.description'))

@section('content')
    @if ($emailVerification)
        <div class="container-page pt-6">
            <div @class(['alert-success' => $emailVerification !== 'invalid', 'alert-danger' => $emailVerification === 'invalid']) role="status">
                <x-store.icon name="{{ $emailVerification === 'invalid' ? 'alert' : 'check' }}" class="mt-0.5" />
                <p>{{ __('storefront.auth.email_verification.'.$emailVerification) }}</p>
            </div>
        </div>
    @endif

    @include('storefront.partials.hero', ['banners' => $heroBanners])

    @php $promoShown = false; @endphp

    @foreach ($sections as $section)
        @php
            $key = $section['key'];
            $title = $section['title'] ?: __('storefront.home.sections.'.$key);
            $items = $section['items'];
            $types = array_unique(array_column($items, 'type'));
            $type = count($types) === 1 ? $types[0] : null;
            $headingId = 'section-'.$key;
        @endphp

        @continue($key === 'hero')

        @if ($key === 'trust_indicators')
            @include('storefront.partials.trust')
            @continue
        @endif

        @if ($key === 'newsletter')
            @include('storefront.partials.newsletter', ['title' => $section['title'], 'subtitle' => $section['subtitle']])
            @continue
        @endif

        @if ($key === 'promo_banners' && $items === [])
            @php $promoShown = true; @endphp
            @include('storefront.partials.promo-banners', ['banners' => $promoBanners])
            @continue
        @endif

        @continue($items === [])

        <section class="container-page pt-14 md:pt-[72px]" aria-labelledby="{{ $headingId }}">
            @if ($type === 'product')
                @php
                    $products = array_column($items, 'data');
                    $categories = array_unique(array_filter(array_map(static fn (array $product): ?string => $product['category']['slug'] ?? null, $products)));
                    $viewAll = count($categories) === 1 ? url()->localized('storefront.category', ['slug' => reset($categories)]) : null;
                @endphp
                <x-store.section-head :title="$title" :href="$viewAll" :id="$headingId" />
                @if ($section['subtitle'])<p class="mt-2 text-sm text-content-secondary">{{ $section['subtitle'] }}</p>@endif
                <div class="-mx-4 mt-6 grid snap-x auto-cols-[72%] grid-flow-col gap-4 overflow-x-auto px-4 pb-2 sm:auto-cols-[45%] md:mx-0 md:grid-flow-row md:grid-cols-2 md:overflow-visible md:px-0 lg:grid-cols-4 lg:gap-6">
                    @foreach (array_slice($products, 0, 4) as $product)
                        <x-store.product-card :product="$product" class="snap-start" />
                    @endforeach
                </div>
            @elseif ($type === 'platform' || $type === 'category')
                <x-store.section-head :title="$title" :href="$type === 'platform' ? url()->localized('storefront.platforms.index') : null" :id="$headingId" />
                <ul class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5 lg:gap-6">
                    @foreach ($items as $item)
                        @php $node = $item['data']; @endphp
                        <li>
                            <a href="{{ $type === 'platform' ? url()->localized('storefront.platforms.show', ['slug' => $node['slug']]) : url()->localized('storefront.category', ['slug' => $node['slug']]) }}" class="block transition duration-200 hover:-translate-y-0.5">
                                <x-store.tile :slug="$node['slug']" :overline="__('storefront.home.'.($type === 'platform' ? 'platform' : 'category'))" :name="$node['name']" :image="$type === 'platform' ? $node['logo'] : $node['banner']" class="h-24 p-3.5 md:h-[100px]" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @elseif ($type === 'banner')
                <x-store.section-head :title="$title" :id="$headingId" />
                <div class="mt-6">
                    @include('storefront.partials.promo-banners', ['banners' => array_column($items, 'data'), 'bare' => true])
                </div>
            @elseif ($type === 'faq')
                <x-store.section-head :title="$title" :href="url()->localized('storefront.pages.show', ['slug' => 'contact'])" :action="__('storefront.home.more_help')" :id="$headingId" />
                <div class="mt-6 flex flex-col gap-2">
                    @foreach ($items as $item)
                        <details class="group rounded-md border border-line-subtle bg-surface-raised open:border-line-default" @if ($loop->first) open @endif>
                            <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-4 px-4 py-3 text-sm font-semibold marker:hidden">
                                {{ $item['data']['question'] }}
                                <x-store.icon name="chevron-down" :size="16" class="text-content-muted transition group-open:rotate-180" />
                            </summary>
                            <p class="px-4 pb-4 text-sm leading-6 whitespace-pre-line text-content-secondary">{{ $item['data']['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            @elseif ($type === 'review')
                <x-store.section-head :title="$title" :id="$headingId" />
                <ul class="mt-6 grid gap-4 md:grid-cols-3 lg:gap-6">
                    @foreach (array_slice($items, 0, 3) as $item)
                        @php $review = $item['data']; @endphp
                        <li class="card flex flex-col gap-3 p-5">
                            <x-store.stars :rating="$review['rating']" />
                            @if ($review['title'])<p class="font-semibold">{{ $review['title'] }}</p>@endif
                            <p class="line-clamp-4 text-sm leading-6 text-content-secondary">{{ $review['body'] }}</p>
                            <div class="mt-auto flex items-center justify-between gap-2 text-sm">
                                <span class="inline-flex items-center gap-2">
                                    <span class="inline-flex size-7 items-center justify-center rounded-full bg-surface-overlay text-xs" aria-hidden="true">{{ mb_strtoupper(mb_substr($review['reviewer'], 0, 2)) }}</span>
                                    {{ $review['reviewer'] }}
                                </span>
                                @if ($review['product'])
                                    <a href="{{ url()->localized('storefront.products.show', ['slug' => $review['product']['slug']]) }}" class="truncate text-xs text-accent-text hover:underline">{{ $review['product']['name'] }}</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endforeach

    @if (! $promoShown && $promoBanners !== [])
        @include('storefront.partials.promo-banners', ['banners' => $promoBanners])
    @endif
@endsection
