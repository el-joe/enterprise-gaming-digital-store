@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-4" aria-label="{{ __('storefront.listing.pagination') }}">
        @if ($paginator->onFirstPage())
            <span class="btn-secondary pointer-events-none opacity-40" aria-disabled="true"><x-store.icon name="chevron-back" :size="16" /> {{ __('storefront.listing.previous') }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-secondary"><x-store.icon name="chevron-back" :size="16" /> {{ __('storefront.listing.previous') }}</a>
        @endif
        <p class="text-sm text-content-secondary">{{ __('storefront.listing.page_of', ['page' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}</p>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-secondary">{{ __('storefront.listing.next') }} <x-store.icon name="chevron-forward" :size="16" /></a>
        @else
            <span class="btn-secondary pointer-events-none opacity-40" aria-disabled="true">{{ __('storefront.listing.next') }} <x-store.icon name="chevron-forward" :size="16" /></span>
        @endif
    </nav>
@endif
