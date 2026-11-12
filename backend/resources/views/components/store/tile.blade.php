{{--
    Media tile (Figma `Brand Card v3` / `Category Card v3`, owner request 2026-11-12): the image
    fills the whole box, with nothing drawn over it (no dial art, no gradient, no text). The
    element's classes size the image box; `flush` drops the box's own rounding and border (the
    image at the top of a product card). A `name` (and an `overline`, shown under the name as the
    muted line, such as a product count) is a caption under the image.

    Image priority: the `image` (a product or category image: `<picture>` with AVIF and WebP,
    every stored width in `srcset`), then the brand `logo` (the brand's full gift-card image,
    shown whole), then the default art (x-store.default-art). Lazy unless `eager`. The whole image
    is always visible (contained, never cropped); a blurred copy of the same image fills any space
    left around it, so wordmarks that are not card-shaped are not cut.
--}}
@props(['slug' => '', 'overline' => null, 'name' => null, 'image' => null, 'logo' => null, 'eager' => false, 'large' => false, 'alt' => null, 'sizes' => null, 'flush' => false])

@php
    $srcset = [];

    foreach (['avif', 'webp'] as $format) {
        $sources = [];

        foreach ((array) ($image['formats'] ?? []) as $key => $url) {
            if (preg_match('/^'.$format.'_(\d+)$/', (string) $key, $match) === 1) {
                $sources[(int) $match[1]] = $url.' '.$match[1].'w';
            }
        }

        if ($sources !== [] && ! empty($image['formats'][$format]) && ! empty($image['width'])) {
            $sources[(int) $image['width']] = $image['formats'][$format].' '.$image['width'].'w';
            ksort($sources);
            $srcset[$format] = implode(', ', $sources);
        }
    }

    $caption = $name !== null && $name !== '' || $overline !== null && $overline !== '';
@endphp

@if ($caption)<div class="flex min-w-0 flex-col gap-2.5">@endif
<div {{ $attributes->class(['relative isolate overflow-hidden bg-surface-raised', 'rounded-lg border border-line-subtle' => ! $flush]) }}>
    @if ($image)
        <img src="{{ $image['formats']['webp_480'] ?? $image['formats']['webp'] ?? $image['url'] }}" alt="" aria-hidden="true" class="absolute inset-0 size-full scale-110 object-cover opacity-70 blur-xl" loading="lazy" decoding="async" data-tile-backdrop>
        <picture class="absolute inset-0">
            @foreach (['avif', 'webp'] as $format)
                @if (! empty($srcset[$format]))<source type="image/{{ $format }}" srcset="{{ $srcset[$format] }}" sizes="{{ $sizes ?? ($large ? '(min-width: 768px) 752px, 100vw' : '(min-width: 1024px) 280px, 50vw') }}">@elseif (! empty($image['formats'][$format]))<source type="image/{{ $format }}" srcset="{{ $image['formats'][$format] }}">@endif
            @endforeach
            <img src="{{ $image['formats']['webp'] ?? $image['url'] }}" alt="{{ $alt ?? $image['alt'] ?? '' }}" @if (! empty($image['width'])) width="{{ $image['width'] }}" height="{{ $image['height'] }}" @endif class="relative size-full object-contain" @if ($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async">
        </picture>
    @elseif ($logo)
        <img src="{{ $logo['formats']['webp_480'] ?? $logo['formats']['webp'] ?? $logo['url'] }}" alt="" aria-hidden="true" class="absolute inset-0 size-full scale-110 object-cover opacity-70 blur-xl" loading="lazy" decoding="async" data-tile-backdrop>
        <picture class="absolute inset-0" data-tile-logo>
            @foreach (['avif', 'webp'] as $format)
                @if (! empty($logo['formats'][$format.'_480'] ?? $logo['formats'][$format] ?? null))<source type="image/{{ $format }}" srcset="{{ $logo['formats'][$format.'_480'] ?? $logo['formats'][$format] }}">@endif
            @endforeach
            <img src="{{ $logo['formats']['webp_480'] ?? $logo['formats']['webp'] ?? $logo['url'] }}" alt="{{ $alt ?? '' }}" class="relative size-full object-contain" @if ($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async">
        </picture>
    @else
        <x-store.default-art />
    @endif
</div>
@if ($caption)
    <div class="min-w-0">
        @if ($name !== null && $name !== '')
            <p @class(['truncate font-semibold text-content-primary', 'text-[15px] leading-5' => ! $large, 'font-display text-2xl font-bold' => $large])>{{ $name }}</p>
        @endif
        @if ($overline !== null && $overline !== '')
            <p class="truncate text-[13px] leading-5 text-content-muted">{{ $overline }}</p>
        @endif
    </div>
</div>
@endif
