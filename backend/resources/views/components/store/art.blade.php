{{--
    Banner art from resources/assets/images (docs/ASSETS.md) through Vite, AVIF first. Banners
    with an `-rtl` mirror get it when the page is right-to-left.
--}}
@props(['path', 'width', 'height', 'alt' => '', 'rtl' => false, 'avif' => true, 'eager' => false])

@php
    $isRtl = $rtl && app()->getLocale() === 'ar';
    $base = $isRtl ? preg_replace('/-(\d+x\d+)$/', '-rtl-$1', $path) : $path;
@endphp

<picture class="contents">
    @if ($avif)<source type="image/avif" srcset="{{ Vite::asset("resources/assets/images/{$base}.avif") }}">@endif
    <img src="{{ Vite::asset("resources/assets/images/{$base}.webp") }}" alt="{{ $alt }}" width="{{ $width }}" height="{{ $height }}" {{ $attributes }} @if ($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async">
</picture>
