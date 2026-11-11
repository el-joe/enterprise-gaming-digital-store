{{-- Popular searches on the 404 and 410 pages (SEO_STRATEGY.md section 7, M18). Never breaks the error page. --}}
@php
    try {
        $popularTerms = array_slice(app(\App\Domains\Catalog\Services\SearchService::class)->popular(app()->getLocale()), 0, 8);
    } catch (\Throwable) {
        $popularTerms = [];
    }
@endphp
@if ($popularTerms !== [])
    <div class="mt-4 flex flex-col items-center gap-2">
        <p class="eyebrow text-content-muted">{{ __('storefront.errors.popular') }}</p>
        <ul class="flex flex-wrap justify-center gap-2">
            @foreach ($popularTerms as $popularTerm)
                <li><a href="{{ url()->localized('storefront.search').'?q='.urlencode($popularTerm) }}" class="chip min-h-8 hover:text-content-primary">{{ $popularTerm }}</a></li>
            @endforeach
        </ul>
    </div>
@endif
