{{--
    Product card (Figma `Product Card`, 6:1065; Storefront v2): key-cut silhouette, the product
    image (x-store.product-image: product image, then brand logo, then the generated art), product
    name, delivery meta, the gift card's face value in its own currency (owner request) with the
    selling price in the display currency under it, and a working bag button: one purchasable
    variant is added at once, several open the variant chooser (resources/js/modules/quick-add.js).
--}}
@props(['product', 'eager' => false])

@php
    $instant = $product['delivery_type'] === 'instant_code';
    $url = url()->localized('storefront.products.show', ['slug' => $product['slug']]);
    $quick = $product['quick_add'] ?? ['variant_count' => 0, 'variant_id' => null, 'requires_fields' => false];
    $face = $product['face_value'] ?? null;
@endphp

<article {{ $attributes->class(['card group relative flex flex-col overflow-hidden transition duration-200 ease-(--ease-brand) hover:-translate-y-0.5 hover:border-accent-primary/60 hover:shadow-glow-volt']) }} data-product-card>
    <x-store.product-image
        :slug="$product['platform']['slug'] ?? $product['slug']"
        :overline="$product['category']['name'] ?? null"
        :label="$product['platform']['name'] ?? null"
        :image="$product['image']"
        :logo="$product['platform']['logo'] ?? null"
        :name="$product['name']"
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
            <div class="min-w-0">
                @if ($face)
                    <p class="font-display text-xl leading-[26px] font-bold" dir="ltr" data-face-value>{{ $face['label'] }}</p>
                    @if ($product['price_from'])
                        <p class="text-xs leading-4 text-content-muted">{{ __('storefront.catalog.pay_from') }} <span dir="ltr">{{ $product['price_from']['formatted'] }}</span></p>
                    @endif
                @elseif ($product['price_from'])
                    @if ($product['price_may_vary'])
                        <p class="text-xs leading-4 text-content-muted">{{ __('storefront.price.from') }}</p>
                    @endif
                    <p class="font-display text-xl leading-[26px] font-bold" dir="ltr">{{ $product['price_from']['formatted'] }}</p>
                @else
                    <p class="text-sm text-content-muted">{{ __('storefront.price.unavailable') }}</p>
                @endif
            </div>
            @if ($quick['variant_count'] > 0)
                <button type="button" class="keycut-sm relative z-10 inline-flex size-11 shrink-0 items-center justify-center bg-accent-primary text-content-on-accent transition hover:bg-accent-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring disabled:opacity-60"
                    data-module="quick-add" data-quick-add data-slug="{{ $product['slug'] }}" data-variant="{{ $quick['variant_id'] }}" data-name="{{ $product['name'] }}"
                    aria-label="{{ __('storefront.catalog.add_to_bag', ['name' => $product['name']]) }}" @if (! $quick['variant_id']) aria-haspopup="dialog" @endif>
                    <x-store.icon name="bag" :size="18" />
                </button>
            @else
                <a href="{{ $url }}" class="keycut-sm relative z-10 inline-flex size-11 shrink-0 items-center justify-center border border-line-default text-content-secondary" aria-label="{{ __('storefront.catalog.view_product', ['name' => $product['name']]) }}">
                    <x-store.icon name="arrow" :size="18" />
                </a>
            @endif
        </div>
    </div>
</article>
