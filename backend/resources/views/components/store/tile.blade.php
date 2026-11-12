{{--
    Colour tile (Figma `Platform Tile`): a plain-text name on a palette field with the dial art. The
    field colour is derived from the slug. A product image (staff upload, or the synced supplier
    image under owner decision 12) replaces the art as the background: `<picture>` with AVIF and
    WebP sources (every stored width in `srcset`), intrinsic size, lazy unless `eager`, and `alt`
    (the product name). Without an image the generated dial art stays.
--}}
@props(['slug' => '', 'overline' => null, 'name' => null, 'image' => null, 'eager' => false, 'large' => false, 'alt' => null, 'sizes' => null])

@php
    $palettes = ['volt', 'ember', 'ultra', 'aqua', 'flux', 'sun'];
    $palette = $palettes[crc32((string) $slug) % count($palettes)];
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
@endphp

<div {{ $attributes->class(['tile-field tile-'.$palette, 'keycut flex flex-col justify-end']) }}>
    @if ($image)
        <picture class="absolute inset-0 -z-10">
            @foreach (['avif', 'webp'] as $format)
                @if (! empty($srcset[$format]))<source type="image/{{ $format }}" srcset="{{ $srcset[$format] }}" sizes="{{ $sizes ?? ($large ? '(min-width: 768px) 752px, 100vw' : '(min-width: 1024px) 240px, 50vw') }}">@elseif (! empty($image['formats'][$format]))<source type="image/{{ $format }}" srcset="{{ $image['formats'][$format] }}">@endif
            @endforeach
            <img src="{{ $image['formats']['webp'] ?? $image['url'] }}" alt="{{ $alt ?? $image['alt'] ?? '' }}" @if (! empty($image['width'])) width="{{ $image['width'] }}" height="{{ $image['height'] }}" @endif class="size-full object-cover" @if ($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async">
        </picture>
        <span class="absolute inset-0 -z-10 bg-linear-to-t from-ink-950/80 to-transparent"></span>
    @else
        <x-store.dial @class([
            'absolute -top-1/2 -end-1/5 aspect-square',
            'h-[150%]' => ! $large,
            'h-[140%]' => $large,
        ]) />
    @endif

    @if ($overline || $name)
        <div class="relative min-w-0">
            @if ($overline)
                <p class="eyebrow text-ink-200">{{ $overline }}</p>
            @endif
            @if ($name)
                <p @class(['truncate font-semibold text-white', 'text-lg leading-[26px]' => ! $large, 'font-display text-3xl font-bold md:text-4xl' => $large])>{{ $name }}</p>
            @endif
        </div>
    @endif
</div>
