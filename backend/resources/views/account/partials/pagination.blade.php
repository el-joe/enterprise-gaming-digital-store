{{-- Pagination of an account list: "Showing x to y of z" and previous / next. --}}
@if ($paginator->total() > 0)
    <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-content-secondary">
        <p>{{ __('account.common.showing', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}</p>
        @include('storefront.partials.pagination', ['paginator' => $paginator])
    </div>
@endif
