{{--
    Brand logo as a responsive <picture> (AVIF first, WebP fallback).

    variant: "full" (box + wordmark) or "mark" (box only, square).
    surface: "dark" renders the transparent logo; "light" wraps it in an ink chip,
             because the white wordmark and volt green fail contrast on light surfaces.
    height:  rendered CSS height in pixels; width follows the intrinsic aspect ratio.
    eager:   true for above-the-fold placements (header) to skip lazy loading.
--}}
@props([
    'variant' => 'full',
    'surface' => 'dark',
    'height' => 36,
    'eager' => false,
])

@php
    $isMark = $variant === 'mark';
    $config = config($isMark ? 'brand.mark' : 'brand.logo');
    [$ratioWidth, $ratioHeight] = $isMark ? [1, 1] : $config['aspect'];
    $height = (int) $height;
    $width = (int) round($height * $ratioWidth / $ratioHeight);

    $srcset = fn (string $extension): string => collect($config['widths'])
        ->map(fn (int $w): string => asset(sprintf($config['path'], $w).'.'.$extension).' '.$w.'w')
        ->implode(', ');

    $fallbackWidth = collect($config['widths'])->first(fn (int $w): bool => $w >= $width * 2) ?? last($config['widths']);
@endphp

<span {{ $attributes->class([
    'inline-flex shrink-0 items-center',
    'rounded-se-[14px] rounded-ss-md rounded-es-md rounded-ee-md bg-[#07080B] px-3 py-2' => $surface === 'light',
]) }}>
    <picture class="contents">
        <source type="image/avif" srcset="{{ $srcset('avif') }}" sizes="{{ $width }}px">
        <source type="image/webp" srcset="{{ $srcset('webp') }}" sizes="{{ $width }}px">
        <img
            src="{{ asset(sprintf($config['path'], $fallbackWidth).'.webp') }}"
            alt="{{ config('brand.name') }}"
            width="{{ $width }}"
            height="{{ $height }}"
            @if ($eager) fetchpriority="high" @else loading="lazy" @endif
            decoding="async"
            class="block h-auto max-w-full"
            style="height: {{ $height }}px; width: auto;"
        >
    </picture>
</span>
