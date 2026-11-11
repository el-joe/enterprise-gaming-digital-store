{{--
    BD8-neutral colour tile (Figma `Platform Tile`): a plain-text name on a palette field with the
    dial art. The field colour is derived from the slug, so it never references a brand identity.
    An admin-uploaded image (product or platform media) replaces the art when present.
--}}
@props(['slug' => '', 'overline' => null, 'name' => null, 'image' => null, 'eager' => false, 'large' => false])

@php
    $palettes = ['volt', 'ember', 'ultra', 'aqua', 'flux', 'sun'];
    $palette = $palettes[crc32((string) $slug) % count($palettes)];
@endphp

<div {{ $attributes->class(['tile-field tile-'.$palette, 'keycut flex flex-col justify-end']) }}>
    @if ($image)
        <picture class="absolute inset-0 -z-10">
            @if (! empty($image['formats']['avif']))<source type="image/avif" srcset="{{ $image['formats']['avif'] }}">@endif
            @if (! empty($image['formats']['webp']))<source type="image/webp" srcset="{{ $image['formats']['webp'] }}">@endif
            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] ?? '' }}" @if (! empty($image['width'])) width="{{ $image['width'] }}" height="{{ $image['height'] }}" @endif class="size-full object-cover" @if ($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async">
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
