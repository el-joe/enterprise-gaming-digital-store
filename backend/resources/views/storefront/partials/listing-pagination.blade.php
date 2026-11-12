{{--
    Crawlable listing pagination (Figma "All products"): previous, numbered page links with gaps,
    next, and "Showing 1–24 of 412". Links carry the current filters (ListingState::pageUrl).
--}}
@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $pages = array_values(array_unique(array_filter([1, $current - 1, $current, $current + 1, $last], static fn (int $page): bool => $page >= 1 && $page <= $last)));
    sort($pages);
@endphp

@if ($last > 1)
    <nav class="flex flex-col items-center gap-3" aria-label="{{ __('storefront.listing.pagination') }}">
        <ul class="flex flex-wrap items-center justify-center gap-2">
            <li>
                @if ($current > 1)
                    <a href="{{ $state->pageUrl($current - 1) }}" rel="prev" class="inline-flex size-11 items-center justify-center rounded-md border border-line-default hover:border-line-strong"><x-store.icon name="chevron-back" :size="16" :label="__('storefront.listing.previous')" /></a>
                @else
                    <span class="inline-flex size-11 items-center justify-center rounded-md border border-line-subtle opacity-40" aria-disabled="true"><x-store.icon name="chevron-back" :size="16" :label="__('storefront.listing.previous')" /></span>
                @endif
            </li>
            @foreach ($pages as $index => $page)
                @if ($index > 0 && $page - $pages[$index - 1] > 1)
                    <li class="inline-flex size-11 items-center justify-center text-content-muted" aria-hidden="true">…</li>
                @endif
                <li>
                    @if ($page === $current)
                        <span class="inline-flex size-11 items-center justify-center rounded-md bg-accent-primary font-semibold text-content-on-accent" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $state->pageUrl($page) }}" class="inline-flex size-11 items-center justify-center rounded-md border border-line-default hover:border-line-strong" aria-label="{{ __('storefront.listing.page', ['page' => $page]) }}">{{ $page }}</a>
                    @endif
                </li>
            @endforeach
            <li>
                @if ($current < $last)
                    <a href="{{ $state->pageUrl($current + 1) }}" rel="next" class="inline-flex size-11 items-center justify-center rounded-md border border-line-default hover:border-line-strong"><x-store.icon name="chevron-forward" :size="16" :label="__('storefront.listing.next')" /></a>
                @else
                    <span class="inline-flex size-11 items-center justify-center rounded-md border border-line-subtle opacity-40" aria-disabled="true"><x-store.icon name="chevron-forward" :size="16" :label="__('storefront.listing.next')" /></span>
                @endif
            </li>
        </ul>
        <p class="text-sm text-content-secondary">{{ __('storefront.catalog.showing', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => number_format($paginator->total())]) }}</p>
    </nav>
@endif
