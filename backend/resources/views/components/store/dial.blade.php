{{--
    The vault dial of the brand art (Figma `Art / Vault Dial`), drawn in currentColor: a ring of
    ticks, a segmented key ring and an inner disc. Decorative only; logo-free (BD8).
--}}
@props(['play' => false])

<svg {{ $attributes->merge(['class' => 'pointer-events-none']) }} viewBox="0 0 200 200" fill="none" aria-hidden="true" focusable="false">
    <circle cx="100" cy="100" r="96" stroke="currentColor" stroke-opacity=".45" stroke-width="5" stroke-dasharray="1.5 6.5"/>
    <circle cx="100" cy="100" r="76" stroke="currentColor" stroke-opacity=".85" stroke-width="20" stroke-dasharray="44 15.7"/>
    <circle cx="100" cy="100" r="58" stroke="currentColor" stroke-opacity=".3" stroke-width="1.5"/>
    <circle cx="100" cy="100" r="54" fill="currentColor" fill-opacity=".08"/>
    <circle cx="100" cy="100" r="44" stroke="currentColor" stroke-opacity=".35" stroke-width="3" stroke-dasharray="1 5"/>
    @if ($play)
        <path d="M88 76v48l40-24-40-24Z" fill="currentColor" fill-opacity=".9"/>
    @endif
</svg>
