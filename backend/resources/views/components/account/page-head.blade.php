@props(['title', 'subtitle' => null])

<header {{ $attributes->class(['flex flex-wrap items-end justify-between gap-3']) }}>
    <div class="min-w-0">
        <h1 class="font-display text-[28px] leading-[34px] font-bold md:text-[32px] md:leading-[38px]">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-content-secondary">{{ $subtitle }}</p>
        @endif
    </div>
    {{ $slot }}
</header>
