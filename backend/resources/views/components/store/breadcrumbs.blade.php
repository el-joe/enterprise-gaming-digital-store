@props(['items'])

<nav aria-label="{{ __('storefront.common.breadcrumb') }}" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-2 text-sm text-content-muted">
        @foreach ($items as $label => $href)
            <li class="flex items-center gap-2">
                @if (! $loop->first)<x-store.icon name="chevron-forward" :size="14" />@endif
                @if ($href && ! $loop->last)
                    <a href="{{ $href }}" class="hover:text-content-primary">{{ $label }}</a>
                @else
                    <span class="text-content-primary" aria-current="page">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
