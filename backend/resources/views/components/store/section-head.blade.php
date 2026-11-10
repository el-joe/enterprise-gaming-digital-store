@props(['title', 'href' => null, 'action' => null, 'level' => 'h2', 'id' => null])

<div {{ $attributes->class(['flex items-end justify-between gap-4']) }}>
    <div class="flex min-w-0 flex-col gap-1.5">
        <span class="block h-3 w-14 bg-[repeating-linear-gradient(90deg,var(--color-accent-primary)_0_2px,transparent_2px_5px)] opacity-90" aria-hidden="true"></span>
        <{{ $level }} @if ($id) id="{{ $id }}" @endif class="font-display text-2xl leading-8 font-bold md:text-[32px] md:leading-[38px]">{{ $title }}</{{ $level }}>
    </div>
    @if ($href)
        <a href="{{ $href }}" class="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-accent-text hover:underline">
            {{ $action ?? __('storefront.common.view_all') }} <x-store.icon name="arrow" :size="16" />
        </a>
    @endif
</div>
