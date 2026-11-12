{{--
    Product card (Figma `Product Card`, 6:1065): key-cut silhouette, colour tile with the platform
    name (the product image as its background when there is one, owner decision 12), product name, delivery meta and the server-formatted price (ProductCardResource).
--}}
@props(['product', 'eager' => false])

@php
    $instant = $product['delivery_type'] === 'instant_code';
    $url = url()->localized('storefront.products.show', ['slug' => $product['slug']]);
@endphp

<article {{ $attributes->class(['card group relative flex flex-col overflow-hidden transition duration-200 ease-(--ease-brand) hover:-translate-y-0.5 hover:border-accent-primary/60 hover:shadow-glow-volt']) }}>
    <x-store.tile
        :slug="$product['platform']['slug'] ?? $product['slug']"
        :overline="$product['category']['name'] ?? null"
        :name="$product['platform']['name'] ?? null"
        :image="$product['image']"
        :alt="$product['name']"
        :eager="$eager"
        class="h-32 p-3.5"
    />
    <div class="flex flex-1 flex-col gap-2 p-3">
        <h3 class="line-clamp-2 min-h-12 text-base leading-6 font-semibold">
            <a href="{{ $url }}" class="after:absolute after:inset-0 focus-visible:outline-none">{{ $product['name'] }}</a>
        </h3>
        <p class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs leading-4">
            <span class="inline-flex items-center gap-1 font-medium text-accent-text">
                <x-store.icon name="{{ $instant ? 'zap' : 'clock' }}" :size="12" />
                {{ __('storefront.delivery.'.$product['delivery_type']) }}
            </span>
            @if ($product['rating_avg'] !== null && (float) $product['rating_avg'] > 0)
                <span class="inline-flex items-center gap-1 text-content-muted"><x-store.icon name="star" :size="12" class="text-sun-500" /> {{ $product['rating_avg'] }}</span>
            @endif
        </p>
        <div class="mt-auto flex items-end justify-between gap-2 pt-1">
            <div>
                @if ($product['price_from'])
                    @if ($product['price_may_vary'])
                        <p class="text-xs leading-4 text-content-muted">{{ __('storefront.price.from') }}</p>
                    @endif
                    <p class="font-display text-xl leading-[26px] font-bold" dir="ltr">{{ $product['price_from']['formatted'] }}</p>
                @else
                    <p class="text-sm text-content-muted">{{ __('storefront.price.unavailable') }}</p>
                @endif
            </div>
            <span class="keycut-sm relative z-10 inline-flex h-9 items-center bg-accent-primary px-3.5 text-content-on-accent" aria-hidden="true">
                <x-store.icon name="bag" :size="16" />
            </span>
        </div>
    </div>
</article>
