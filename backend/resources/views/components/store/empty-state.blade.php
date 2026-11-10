{{-- Original empty-state art (docs/ASSETS.md), inlined so it follows currentColor. --}}
@props(['art', 'title', 'text' => null])

<div {{ $attributes->class(['flex flex-col items-center gap-3 py-12 text-center']) }}>
    <span class="block w-48 text-content-muted md:w-60">{!! file_get_contents(resource_path("assets/images/empty-states/{$art}-240x200.svg")) !!}</span>
    <h2 class="font-display text-xl font-bold">{{ $title }}</h2>
    @if ($text)
        <p class="max-w-md text-sm text-content-secondary">{{ $text }}</p>
    @endif
    {{ $slot }}
</div>
