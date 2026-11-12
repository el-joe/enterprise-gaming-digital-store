{{--
    Default image (Figma `Default Art`, Cards v3, owner request 2026-11-12): shown when a product,
    brand or category has no image of its own. A flat raised surface with a centred gift-card
    outline; no dial and no gradient. Pure markup on the colour tokens, so it follows the theme.
--}}
<span {{ $attributes->class(['absolute inset-0 flex items-center justify-center bg-surface-raised']) }} data-default-art aria-hidden="true">
    <span class="relative block aspect-[8/5] w-2/5 max-w-24 min-w-7 rounded-md border-2 border-line-strong">
        <span class="absolute start-[12%] top-[18%] h-[20%] w-[17%] rounded-[2px] bg-accent-primary"></span>
        <span class="absolute start-[12%] bottom-[22%] h-[7%] w-1/2 rounded-full bg-line-strong"></span>
    </span>
</span>
