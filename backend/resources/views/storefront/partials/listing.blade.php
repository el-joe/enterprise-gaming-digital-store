{{--
    Storefront v2 listing (Figma "All products" 110:852-110:3086, "Empty" 111:2729, "Loading"
    111:3804, "Filter sheet" 111:4738): a 280 px sidebar of accordions (Category, Brand with search,
    Region or country, Price range with a slider in the display currency, Delivery type,
    Availability), each with its selected count and a count per option; applied chips with
    "Clear all"; the result count and Sort; a 4-column grid (2 on mobile) and crawlable page links.
    Mobile: a sticky Filters / Sort bar and a bottom sheet with a sticky "Apply (N results)".
    A plain GET form (works without JavaScript); resources/js/modules/filters.js writes the
    filters to the URL as comma lists, submits on change on desktop and counts in the sheet.
    Needs: $products, $facets, $state, $currency.
--}}
@php
    $total = $products->total();
    $selected = $facets->selectedCounts();
    $chips = $state->chips($facets);
    $sorts = ['best_selling', 'newest', 'price_asc', 'price_desc'];
    $price = $facets->price;
    $visible = 5;
@endphp

<div class="mt-6 grid gap-6 lg:mt-8 lg:grid-cols-[280px_minmax(0,1fr)] lg:gap-8" data-module="filters"
    data-base-url="{{ $state->baseUrl }}" data-facets-url="{{ url('/api/v1/storefront/catalog/facets') }}" data-hide="{{ implode(',', $facets->hidden) }}"
    data-apply-label="{{ trans_choice('storefront.catalog.apply_count', 2, ['count' => '__count__']) }}"
    data-apply-one="{{ trans_choice('storefront.catalog.apply_count', 1, ['count' => 1]) }}"
    data-apply-none="{{ trans_choice('storefront.catalog.apply_count', 0, ['count' => 0]) }}">

    {{-- Mobile: sticky Filters (count) and Sort bar. --}}
    <div class="sticky top-0 z-30 -mx-4 flex gap-2 border-b border-line-subtle bg-surface-base/95 px-4 py-2 backdrop-blur md:-mx-8 md:px-8 lg:hidden">
        <button type="button" class="btn-secondary min-h-12 flex-1" data-filter-open aria-controls="filter-panel" aria-expanded="false">
            <x-store.icon name="sliders" :size="18" /> {{ __('storefront.catalog.filters') }}
            @if ($facets->selectedTotal() > 0)<span class="rounded-full bg-accent-tint px-2 text-xs text-accent-text">{{ $facets->selectedTotal() }}</span>@endif
        </button>
        <label class="relative flex-1">
            <span class="sr-only">{{ __('storefront.catalog.sort_label') }}</span>
            <select name="sort" form="listing-filters" class="field-input min-h-12 appearance-none pe-9" data-auto-submit>
                @foreach ($sorts as $sort)
                    <option value="{{ $sort }}" @selected($state->sort === $sort)>{{ __('storefront.catalog.sort.'.$sort) }}</option>
                @endforeach
            </select>
            <x-store.icon name="chevron-down" :size="16" class="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-content-muted" />
        </label>
    </div>

    <div class="fixed inset-0 z-40 bg-black/60 lg:hidden" data-filter-backdrop hidden></div>

    <form id="listing-filters" method="get" action="{{ $state->baseUrl }}" class="contents" data-filter-form>
        @if ($state->onSale)<input type="hidden" name="on_sale" value="1">@endif
        <aside id="filter-panel" aria-labelledby="filters-title"
            class="flex-col self-start max-lg:z-50 max-lg:fixed max-lg:inset-x-0 max-lg:bottom-0 max-lg:hidden max-lg:max-h-[92vh] max-lg:rounded-t-2xl max-lg:border-t max-lg:border-line-default max-lg:bg-surface-base max-lg:data-[open]:flex lg:sticky lg:top-28 lg:flex lg:max-h-[calc(100vh-8rem)] lg:rounded-xl lg:border lg:border-line-subtle lg:bg-surface-raised"
            data-filter-panel>
            <div class="flex items-center justify-between gap-3 border-b border-line-subtle px-4 py-3 lg:px-4">
                <h2 id="filters-title" class="text-lg font-semibold lg:text-base">{{ __('storefront.catalog.filters') }}</h2>
                <div class="flex items-center gap-1">
                    @if ($facets->selectedTotal() > 0)
                        <a href="{{ $state->clearUrl() }}" class="inline-flex min-h-11 items-center px-2 text-sm font-medium text-accent-text hover:underline">{{ __('storefront.catalog.clear_all') }}</a>
                    @endif
                    <button type="button" class="inline-flex size-11 items-center justify-center rounded-md lg:hidden" data-filter-close>
                        <x-store.icon name="x" :label="__('storefront.catalog.close_filters')" />
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-4 lg:overflow-y-auto">
                @foreach (['category', 'platform', 'region', 'price', 'delivery_type', 'in_stock'] as $facet)
                    @continue(in_array($facet, ['category', 'platform', 'region', 'delivery_type'], true) && ! array_key_exists($facet, $facets->options))
                    @continue(in_array($facet, ['category', 'platform', 'region', 'delivery_type'], true) && $facets->options[$facet] === [])
                    @continue($facet === 'price' && $price['min'] === null && $price['selected_min'] === null && $price['selected_max'] === null)
                    @php $regionId = 'facet-'.$facet; @endphp
                    <section class="border-b border-line-subtle py-3 last:border-b-0" data-facet="{{ $facet }}">
                        <h3>
                            <button type="button" class="flex min-h-11 w-full items-center justify-between gap-2 text-start text-base font-semibold" aria-expanded="true" aria-controls="{{ $regionId }}" data-accordion-toggle>
                                <span>{{ __('storefront.catalog.facet.'.$facet) }}</span>
                                <span class="flex items-center gap-2">
                                    @if (($selected[$facet] ?? 0) > 0)
                                        <span class="rounded-xs bg-accent-tint px-1.5 text-xs leading-5 text-accent-text" aria-label="{{ __('storefront.catalog.selected_count', ['count' => $selected[$facet]]) }}">{{ $selected[$facet] }}</span>
                                    @endif
                                    <x-store.icon name="chevron-down" :size="16" class="rotate-180 text-content-muted transition" data-accordion-icon />
                                </span>
                            </button>
                        </h3>
                        <div id="{{ $regionId }}" class="pt-1">
                            @if (in_array($facet, ['category', 'platform', 'region', 'delivery_type'], true))
                                @php
                                    $options = $facets->options[$facet];
                                    $first = array_values(array_filter($options, static fn (array $option, int $index): bool => $index < $visible || $option['selected'], ARRAY_FILTER_USE_BOTH));
                                    $rest = array_values(array_filter($options, static fn (array $option, int $index): bool => $index >= $visible && ! $option['selected'], ARRAY_FILTER_USE_BOTH));
                                @endphp
                                <fieldset>
                                    <legend class="sr-only">{{ __('storefront.catalog.facet.'.$facet) }}</legend>
                                    @if ($facet === 'platform' && count($options) > $visible)
                                        <div class="relative mb-2">
                                            <label for="brand-search" class="sr-only">{{ __('storefront.catalog.search_brands') }}</label>
                                            <x-store.icon name="search" :size="16" class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-content-muted" />
                                            <input id="brand-search" type="search" autocomplete="off" class="field-input min-h-11 ps-9" placeholder="{{ __('storefront.catalog.search_brands') }}" data-facet-search>
                                        </div>
                                    @endif
                                    <ul class="flex flex-col" data-facet-options>
                                        @foreach ($first as $option)
                                            @include('storefront.partials.facet-option', ['name' => $facet, 'option' => $option])
                                        @endforeach
                                    </ul>
                                    @if ($rest !== [])
                                        <details class="group" data-facet-more>
                                            <summary class="flex min-h-11 cursor-pointer list-none items-center text-sm font-medium text-accent-text marker:hidden group-open:hidden">
                                                {{ $facet === 'platform' ? __('storefront.catalog.show_all_brands', ['count' => count($options)]) : __('storefront.catalog.show_more', ['count' => count($rest)]) }}
                                            </summary>
                                            <ul class="flex flex-col" data-facet-options>
                                                @foreach ($rest as $option)
                                                    @include('storefront.partials.facet-option', ['name' => $facet, 'option' => $option])
                                                @endforeach
                                            </ul>
                                        </details>
                                    @endif
                                </fieldset>
                            @elseif ($facet === 'price')
                                <fieldset data-price-range data-min="{{ $price['min'] ?? 0 }}" data-max="{{ $price['max'] ?? 0 }}">
                                    <legend class="sr-only">{{ __('storefront.catalog.facet.price') }}</legend>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label for="price-min" class="field-label">{{ __('storefront.catalog.min') }}</label>
                                            <input id="price-min" name="min_price" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" value="{{ $state->minPrice }}" placeholder="{{ $price['min'] }}" class="field-input min-h-11" dir="ltr" aria-label="{{ __('storefront.catalog.price_min', ['currency' => $price['currency']]) }}" data-price-input="min">
                                        </div>
                                        <div>
                                            <label for="price-max" class="field-label">{{ __('storefront.catalog.max') }}</label>
                                            <input id="price-max" name="max_price" type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" value="{{ $state->maxPrice }}" placeholder="{{ $price['max'] }}" class="field-input min-h-11" dir="ltr" aria-label="{{ __('storefront.catalog.price_max', ['currency' => $price['currency']]) }}" data-price-input="max">
                                        </div>
                                    </div>
                                    @if ($price['min'] !== null && $price['max'] !== null && $price['min'] !== $price['max'])
                                        <div class="price-slider relative mt-4 h-6" dir="ltr" data-price-slider hidden>
                                            <input type="range" min="{{ $price['min'] }}" max="{{ $price['max'] }}" step="1" value="{{ $state->minPrice ?? $price['min'] }}" aria-label="{{ __('storefront.catalog.price_min', ['currency' => $price['currency']]) }}" data-slider="min">
                                            <input type="range" min="{{ $price['min'] }}" max="{{ $price['max'] }}" step="1" value="{{ $state->maxPrice ?? $price['max'] }}" aria-label="{{ __('storefront.catalog.price_max', ['currency' => $price['currency']]) }}" data-slider="max">
                                        </div>
                                    @endif
                                    <p class="field-help">{{ __('storefront.catalog.price_help', ['currency' => $price['currency']]) }}</p>
                                    <button type="submit" class="btn-secondary mt-2 min-h-11 w-full" data-price-apply>{{ __('storefront.catalog.apply_price') }}</button>
                                </fieldset>
                            @else
                                <label class="flex min-h-11 cursor-pointer items-center gap-3 text-sm">
                                    <span class="relative inline-flex h-6 w-11 shrink-0">
                                        <input type="checkbox" name="in_stock" value="1" role="switch" class="peer sr-only" @checked($facets->inStock['selected']) data-auto-submit>
                                        <span class="absolute inset-0 rounded-full bg-ink-600 transition peer-checked:bg-accent-primary peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-focus-ring"></span>
                                        <span class="absolute start-0.5 top-0.5 size-5 rounded-full bg-white transition peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
                                    </span>
                                    <span class="flex-1">{{ __('storefront.catalog.in_stock_only') }}</span>
                                    <span class="font-mono text-xs text-content-muted" dir="ltr">{{ $facets->inStock['count'] }}</span>
                                </label>
                            @endif
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="sticky bottom-0 border-t border-line-subtle bg-surface-base p-4 lg:hidden">
                <button type="submit" class="btn-primary btn-lg w-full" data-apply-count>{{ trans_choice('storefront.catalog.apply_count', $total, ['count' => number_format($total)]) }}</button>
            </div>
            <div class="hidden border-t border-line-subtle p-4 lg:block" data-nojs-apply>
                <button type="submit" class="btn-primary w-full">{{ __('storefront.catalog.apply') }}</button>
            </div>
        </aside>
    </form>

    <div class="min-w-0" data-listing-results>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-base font-semibold" role="status">{{ trans_choice('storefront.catalog.results', $total, ['count' => number_format($total)]) }}</p>
            <label class="hidden items-center gap-3 text-sm text-content-secondary lg:flex">
                <span>{{ __('storefront.catalog.sort_label') }}</span>
                <span class="relative">
                    <select name="sort" form="listing-filters" class="field-input min-h-11 w-56 appearance-none pe-9" data-auto-submit>
                        @foreach ($sorts as $sort)
                            <option value="{{ $sort }}" @selected($state->sort === $sort)>{{ __('storefront.catalog.sort.'.$sort) }}</option>
                        @endforeach
                    </select>
                    <x-store.icon name="chevron-down" :size="16" class="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-content-muted" />
                </span>
            </label>
        </div>

        @if ($chips !== [])
            <ul class="mt-4 flex flex-wrap items-center gap-2" aria-label="{{ __('storefront.catalog.applied') }}">
                @foreach ($chips as $chip)
                    <li>
                        <a href="{{ $chip['url'] }}" class="inline-flex min-h-9 items-center gap-1.5 rounded-xs border border-accent-primary/60 bg-accent-tint px-2.5 text-xs font-medium text-accent-text hover:border-accent-primary" data-filter-chip>
                            <span>{{ $chip['label'] }}</span>
                            <x-store.icon name="x" :size="14" :label="__('storefront.catalog.remove', ['name' => $chip['label']])" />
                        </a>
                    </li>
                @endforeach
                <li><a href="{{ $state->clearUrl() }}" class="inline-flex min-h-9 items-center px-1 text-xs font-medium text-accent-text hover:underline">{{ __('storefront.catalog.clear_all') }}</a></li>
            </ul>
        @endif

        <div class="mt-4" data-listing-grid aria-live="polite">
            @if ($products->isEmpty())
                <x-store.empty-state art="no-search-results" :title="__('storefront.catalog.empty_title')" :text="__('storefront.catalog.empty_text')" data-listing-empty>
                    <div class="mt-2 flex flex-wrap justify-center gap-3">
                        <a href="{{ $state->clearUrl() }}" class="btn-primary">{{ __('storefront.catalog.clear_filters') }}</a>
                        <a href="{{ url()->localized('storefront.home') }}#categories" class="btn-secondary">{{ __('storefront.catalog.browse_categories') }}</a>
                    </div>
                </x-store.empty-state>
            @else
                <ul class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <li><x-store.product-card :product="$product" :eager="$loop->index < 4" class="h-full" /></li>
                    @endforeach
                </ul>
                <div class="mt-8">
                    @include('storefront.partials.listing-pagination', ['paginator' => $products, 'state' => $state])
                </div>
            @endif
        </div>

        <template data-skeleton>
            <ul class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 xl:grid-cols-4" aria-busy="true" aria-label="{{ __('storefront.catalog.loading') }}">
                @for ($index = 0; $index < 8; $index++)
                    <li class="card flex flex-col overflow-hidden" aria-hidden="true">
                        <span class="block h-32 animate-pulse bg-surface-overlay"></span>
                        <span class="m-3 block h-4 w-3/4 animate-pulse rounded-full bg-surface-overlay"></span>
                        <span class="mx-3 block h-3 w-1/2 animate-pulse rounded-full bg-surface-overlay"></span>
                        <span class="m-3 mt-6 flex items-end justify-between"><span class="block h-6 w-20 animate-pulse rounded-full bg-surface-overlay"></span><span class="block size-11 animate-pulse rounded-md bg-surface-overlay"></span></span>
                    </li>
                @endfor
            </ul>
        </template>
    </div>
</div>
