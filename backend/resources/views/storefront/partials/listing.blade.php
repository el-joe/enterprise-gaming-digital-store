{{--
    Filters, sort, product grid and pagination for a category or platform listing. Filters are a
    plain GET form (works without JavaScript); `filters.js` submits on change and opens the
    mobile filter sheet. `$fixed` is the page's own filter (category or platform), not shown.
--}}
@php
    $sorts = ['popular', 'newest', 'price_asc', 'price_desc', 'name'];
    $deliveryTypes = ['instant_code', 'direct_topup', 'account_recharge', 'subscription_activation', 'manual'];
    $active = array_filter([
        'platform' => $fixed === 'platform' ? null : $filters['platform'],
        'category' => $fixed === 'category' ? null : $filters['category'],
        'delivery_type' => $filters['delivery_type'],
        'min_price' => $filters['min_price'],
        'max_price' => $filters['max_price'],
    ]);
    $base = url()->current();
@endphp

<form method="get" action="{{ $base }}" class="mt-8 grid gap-6 lg:grid-cols-[280px_1fr] lg:gap-8" data-module="filters">
    <div class="flex items-center justify-between gap-3 lg:col-span-2 lg:justify-end">
        <button type="button" class="btn-secondary lg:hidden" data-filter-toggle aria-controls="filter-panel" aria-expanded="false">
            <x-store.icon name="filter" :size="18" /> {{ __('storefront.listing.filters') }}
            @if ($active !== [])<span class="rounded-full bg-accent-primary px-1.5 text-xs text-content-on-accent">{{ count($active) }}</span>@endif
        </button>
        <label class="flex items-center gap-2 text-sm text-content-secondary">
            <span>{{ __('storefront.listing.sort_by') }}</span>
            <select name="sort" class="field-input h-10 min-h-10 w-auto min-w-44" data-auto-submit>
                @foreach ($sorts as $sort)
                    <option value="{{ $sort }}" @selected($filters['sort'] === $sort)>{{ __('storefront.listing.sort.'.$sort) }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <aside id="filter-panel" class="hidden self-start rounded-xl border border-line-subtle bg-surface-raised p-4 data-[open]:block lg:block" data-filter-panel aria-label="{{ __('storefront.listing.filters') }}">
        <div class="flex flex-col divide-y divide-line-subtle">
            @if ($fixed !== 'platform' && $platforms !== [])
                <fieldset class="pb-4">
                    <legend class="mb-3 text-base font-semibold">{{ __('storefront.listing.platform') }}</legend>
                    <div class="flex flex-col gap-2">
                        <label class="flex min-h-8 items-center gap-3 text-sm"><input type="radio" name="platform" value="" class="size-4 accent-(--color-accent-primary)" @checked(! $filters['platform']) data-auto-submit> {{ __('storefront.listing.all') }}</label>
                        @foreach ($platforms as $platform)
                            @continue($platform['product_count'] === 0)
                            <label class="flex min-h-8 items-center justify-between gap-3 text-sm">
                                <span class="flex items-center gap-3"><input type="radio" name="platform" value="{{ $platform['slug'] }}" class="size-4 accent-(--color-accent-primary)" @checked($filters['platform'] === $platform['slug']) data-auto-submit> {{ $platform['name'] }}</span>
                                <span class="text-xs text-content-muted">{{ $platform['product_count'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            @if ($fixed !== 'category' && $categories !== [])
                <fieldset class="py-4">
                    <legend class="mb-3 text-base font-semibold">{{ __('storefront.listing.category') }}</legend>
                    <select name="category" class="field-input" data-auto-submit>
                        <option value="">{{ __('storefront.listing.all') }}</option>
                        @foreach ($categories as $category)
                            @continue($category['product_count'] === 0)
                            <option value="{{ $category['slug'] }}" @selected($filters['category'] === $category['slug'])>{{ $category['name'] }} ({{ $category['product_count'] }})</option>
                        @endforeach
                    </select>
                </fieldset>
            @endif

            <fieldset class="py-4">
                <legend class="mb-3 text-base font-semibold">{{ __('storefront.listing.price') }}</legend>
                <div class="grid grid-cols-2 gap-2">
                    <label class="text-xs text-content-secondary">{{ __('storefront.listing.min') }}
                        <input type="text" inputmode="decimal" name="min_price" value="{{ $filters['min_price'] }}" class="field-input mt-1.5" pattern="\d+(\.\d{1,2})?" dir="ltr">
                    </label>
                    <label class="text-xs text-content-secondary">{{ __('storefront.listing.max') }}
                        <input type="text" inputmode="decimal" name="max_price" value="{{ $filters['max_price'] }}" class="field-input mt-1.5" pattern="\d+(\.\d{1,2})?" dir="ltr">
                    </label>
                </div>
                <p class="field-help">{{ __('storefront.listing.price_help', ['currency' => $currency]) }}</p>
            </fieldset>

            <fieldset class="py-4">
                <legend class="mb-3 text-base font-semibold">{{ __('storefront.listing.delivery') }}</legend>
                <div class="flex flex-col gap-2">
                    <label class="flex min-h-8 items-center gap-3 text-sm"><input type="radio" name="delivery_type" value="" class="size-4 accent-(--color-accent-primary)" @checked(! $filters['delivery_type']) data-auto-submit> {{ __('storefront.listing.all') }}</label>
                    @foreach ($deliveryTypes as $deliveryType)
                        <label class="flex min-h-8 items-center gap-3 text-sm"><input type="radio" name="delivery_type" value="{{ $deliveryType }}" class="size-4 accent-(--color-accent-primary)" @checked($filters['delivery_type'] === $deliveryType) data-auto-submit> {{ __('storefront.delivery.'.$deliveryType) }}</label>
                    @endforeach
                </div>
            </fieldset>

            <div class="flex gap-2 pt-4">
                <button type="submit" class="btn-primary flex-1">{{ __('storefront.listing.apply') }}</button>
                <a href="{{ $base }}" class="btn-ghost">{{ __('storefront.listing.clear') }}</a>
            </div>
        </div>
    </aside>

    <div>
        @if ($active !== [])
            <ul class="mb-4 flex flex-wrap items-center gap-2" aria-label="{{ __('storefront.listing.active_filters') }}">
                @foreach ($active as $name => $value)
                    <li>
                        <a href="{{ $base.'?'.http_build_query(array_filter(array_merge(request()->except(['page', $name]), []))) }}" class="inline-flex items-center gap-1 rounded-xs bg-accent-tint px-2 py-1 text-xs font-medium text-accent-text">
                            {{ __('storefront.listing.filter.'.$name) }}: {{ $name === 'delivery_type' ? __('storefront.delivery.'.$value) : $value }}
                            <x-store.icon name="x" :size="12" :label="__('storefront.listing.remove_filter')" />
                        </a>
                    </li>
                @endforeach
                <li><a href="{{ $base }}" class="text-xs text-content-secondary hover:text-content-primary">{{ __('storefront.listing.clear') }}</a></li>
            </ul>
        @endif

        @if ($products->isEmpty())
            <x-store.empty-state art="no-search-results" :title="__('storefront.listing.empty_title')" :text="__('storefront.listing.empty_text')">
                <a href="{{ $base }}" class="btn-secondary mt-2">{{ __('storefront.listing.clear') }}</a>
            </x-store.empty-state>
        @else
            <ul class="grid grid-cols-1 gap-4 min-[420px]:grid-cols-2 md:grid-cols-2 xl:grid-cols-3 xl:gap-6">
                @foreach ($products as $product)
                    <li><x-store.product-card :product="$product" class="h-full" /></li>
                @endforeach
            </ul>
            <div class="mt-8">
                @include('storefront.partials.pagination', ['paginator' => $products])
            </div>
        @endif
    </div>
</form>
