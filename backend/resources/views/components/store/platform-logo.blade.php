{{--
    A platform's logo (staff upload or the supplier's brand logo, owner request 2026-11-12) as a
    small `<picture>` with the 480 px AVIF and WebP. Renders nothing without an image, so the
    caller's own text or the generated art stays. `alt` is empty by default because the platform
    name is always next to it.
--}}
@props(['image' => null, 'alt' => '', 'eager' => false])

@if ($image)
    <picture {{ $attributes->class(['inline-block shrink-0']) }}>
        @foreach (['avif', 'webp'] as $format)
            @if (! empty($image['formats'][$format.'_480'] ?? $image['formats'][$format] ?? null))<source type="image/{{ $format }}" srcset="{{ $image['formats'][$format.'_480'] ?? $image['formats'][$format] }}">@endif
        @endforeach
        <img src="{{ $image['formats']['webp_480'] ?? $image['formats']['webp'] ?? $image['url'] }}" alt="{{ $alt }}" class="size-full rounded-xs object-contain" @if ($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async">
    </picture>
@endif
