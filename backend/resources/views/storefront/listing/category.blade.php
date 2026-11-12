@extends('layouts.storefront')

{{--
    Category page `/c/{slug}` (Storefront v2, Figma 112:4207): header with the category's active
    image (supplier or staff), intro and top-brand chips; the listing without the Category facet.
    Canonical and robots: SEO_STRATEGY.md section 2 (an admin robots value or canonical wins, M18).
--}}
@section('title', $seo->title.($page > 1 ? ' — '.__('storefront.listing.page', ['page' => $page]) : ''))
@section('description', \Illuminate\Support\Str::limit((string) $seo->description, 155))
@section('robots', $seo->robots ?: $robots)
@section('canonical', $seo->canonical ?: $canonical)

@section('content')
    <div class="container-page pt-6 md:pt-8">
        <x-store.breadcrumbs :items="[__('storefront.nav.home_short') => url()->localized('storefront.home'), $category['name'] => null]" />

        <section class="keycut tile-field tile-{{ ['volt', 'ember', 'ultra', 'aqua', 'flux', 'sun'][crc32($category['slug']) % 6] }} relative isolate mt-4 overflow-hidden border border-line-subtle">
            @if ($category['banner'])
                <picture class="absolute inset-0 -z-10">
                    @foreach (['avif', 'webp'] as $format)
                        @if (! empty($category['banner']['formats'][$format]))<source type="image/{{ $format }}" srcset="{{ $category['banner']['formats'][$format] }}">@endif
                    @endforeach
                    <img src="{{ $category['banner']['formats']['webp'] ?? $category['banner']['url'] }}" alt="" class="size-full object-cover" fetchpriority="high">
                </picture>
                <div class="absolute inset-0 -z-10 bg-linear-to-r from-ink-950 via-ink-950/75 to-transparent rtl:bg-linear-to-l" aria-hidden="true"></div>
            @else
                <x-store.dial class="absolute -end-10 -top-1/3 -z-10 hidden aspect-square h-[160%] opacity-80 md:block" />
            @endif
            <div class="flex min-h-48 max-w-2xl flex-col justify-center gap-3 p-6 md:p-10">
                <p class="eyebrow text-accent-text">{{ __('storefront.catalog.category_eyebrow') }}</p>
                <h1 class="font-display text-[32px] leading-[38px] font-bold text-white md:text-[40px] md:leading-[46px]">{{ $category['name'] }}</h1>
                @if ($category['description'])
                    <p class="line-clamp-3 text-sm leading-6 text-ink-200 md:text-base">{{ $category['description'] }}</p>
                @endif
                <p class="text-xs text-ink-200">{{ trans_choice('storefront.catalog.products_label', $products->total(), ['count' => number_format($products->total())]) }} · {{ trans_choice('storefront.catalog.brands_count', $brandCount, ['count' => $brandCount]) }}</p>
                @if ($topBrands !== [])
                    <ul class="flex flex-wrap gap-2" aria-label="{{ __('storefront.catalog.top_brands_in', ['name' => $category['name']]) }}">
                        @foreach ($topBrands as $brand)
                            <li><a href="{{ url()->localized('storefront.brand', ['slug' => $brand['slug']]) }}" class="inline-flex h-11 items-center rounded-full bg-ink-950/70 px-4 text-sm font-medium text-white hover:bg-ink-950">{{ $brand['name'] }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        @include('storefront.partials.listing')
    </div>
@endsection
