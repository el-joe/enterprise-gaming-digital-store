{{-- A panel (Figma admin card): optional title, subtitle and header actions. --}}
@props(['title' => null, 'subtitle' => null, 'padded' => true])

<section {{ $attributes->class(['admin-card min-w-0']) }}>
    @if ($title || isset($actions))
        <div class="flex flex-wrap items-start justify-between gap-3 px-5 pt-5 {{ $padded ? '' : 'pb-4' }}">
            <div class="min-w-0">
                @if ($title)<h2 class="text-base leading-6 font-semibold md:text-lg">{{ $title }}</h2>@endif
                @if ($subtitle)<p class="mt-0.5 text-xs text-content-muted">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
        </div>
    @endif
    <div @class(['p-5' => $padded, 'pt-3' => $padded && ($title || isset($actions))])>{{ $slot }}</div>
</section>
