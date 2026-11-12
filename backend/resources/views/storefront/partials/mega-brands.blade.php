{{-- One mega-menu panel (Figma 109:371): top brand tiles, "See all in …" and popular filters. --}}
<div class="flex items-start justify-between gap-4">
    <div class="min-w-0">
        <h3 class="truncate text-lg leading-7 font-semibold">{{ $title }}</h3>
        <p class="text-sm text-content-muted">{{ $meta }}</p>
    </div>
    <a href="{{ $href }}" class="inline-flex min-h-11 shrink-0 items-center gap-1.5 text-sm font-medium text-accent-text hover:underline">{{ $action }} <x-store.icon name="arrow" :size="16" class="rtl:-scale-x-100" /></a>
</div>
@if ($brands !== [])
    <ul class="mt-4 grid grid-cols-4 gap-3">
        @foreach ($brands as $brand)
            <li>
                <a href="{{ url()->localized('storefront.brand', ['slug' => $brand['slug']]) }}" class="block transition hover:-translate-y-0.5" data-mega-brand>
                    <x-store.tile :slug="$brand['slug']" :logo="$brand['logo']" :name="$brand['name']" :alt="__('storefront.catalog.logo', ['name' => $brand['name']])" class="h-24 p-3" />
                </a>
            </li>
        @endforeach
    </ul>
@endif
@if ($filters !== [])
    <p class="mt-6 text-sm text-content-muted">{{ __('storefront.catalog.popular_filters') }}</p>
    <ul class="mt-2 flex flex-wrap gap-2">
        @foreach ($filters as $filter)
            <li><a href="{{ $filter['href'] }}" class="inline-flex h-11 items-center rounded-full border border-line-default px-4 text-sm font-medium hover:border-line-strong">{{ $filter['label'] }}</a></li>
        @endforeach
    </ul>
@endif
