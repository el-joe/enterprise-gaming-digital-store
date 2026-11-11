{{-- One breadcrumb: a link, or the current page when `href` is null. --}}
@props(['href' => null])

<li class="flex items-center gap-1.5">
    @if ($href)
        <a href="{{ $href }}" class="hover:text-content-primary">{{ $slot }}</a>
        <x-store.icon name="chevron-forward" :size="14" />
    @else
        <span class="font-medium text-content-primary" aria-current="page">{{ $slot }}</span>
    @endif
</li>
