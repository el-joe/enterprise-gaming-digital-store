{{-- Product buy box (variables from storefront/product.blade.php). --}}
<div class="lg:sticky lg:top-24 lg:col-start-2 lg:row-span-2 lg:row-start-1 lg:self-start">
                <form class="flex flex-col gap-6" data-module="product" data-product-form data-item-name="{{ $product['name'] }}" data-item-category="{{ $product['category']['name'] ?? '' }}" data-item-brand="{{ $product['platform']['name'] ?? '' }}" novalidate>
                    <div>
                        <div class="flex flex-wrap gap-2">
                            @if ($product['platform']['slug'])
                                <a href="{{ url()->localized('storefront.platforms.show', ['slug' => $product['platform']['slug']]) }}" class="chip hover:text-content-primary">{{ $product['platform']['name'] }}</a>
                            @endif
                            @if ($product['category']['slug'])
                                <a href="{{ url()->localized('storefront.category', ['slug' => $product['category']['slug']]) }}" class="chip hover:text-content-primary">{{ $product['category']['name'] }}</a>
                            @endif
                        </div>
                        <h1 class="mt-3 font-display text-[28px] leading-[34px] font-bold md:text-[32px] md:leading-[38px]">{{ $product['name'] }}</h1>
                        <p class="mt-2 font-mono text-xs text-content-muted" dir="ltr">{{ __('storefront.product.sku') }} <span data-variant-sku>{{ $selected['sku'] ?? $product['sku'] }}</span></p>
                        @if ($product['rating']['count'] > 0)
                            <a href="#reviews" class="mt-2 inline-flex items-center gap-2 text-sm text-content-secondary"><x-store.stars :rating="$product['rating']['average']" /> {{ trans_choice('storefront.reviews.count', $product['rating']['count'], ['count' => $product['rating']['count']]) }}</a>
                        @endif
                    </div>

                    @if ($variants === [])
                        <div class="alert-warning"><x-store.icon name="alert" class="mt-0.5" /><p>{{ __('storefront.product.no_variants') }}</p></div>
                    @else
                        @if (count($regions) > 1 || $regions[0] !== null)
                            <fieldset>
                                <div class="mb-3 flex items-center justify-between gap-2">
                                    <legend class="text-lg font-semibold">{{ __('storefront.product.region') }}</legend>
                                    <span class="text-xs text-content-muted">{{ __('storefront.product.region_hint') }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                    @foreach ($regions as $region)
                                        @php $regionAvailable = collect($variants)->where('region', $region)->contains('purchasable', true); @endphp
                                        <button type="button" data-region-option="{{ $region ?? 'global' }}" aria-pressed="{{ $region === $selectedRegion ? 'true' : 'false' }}" @disabled(! $regionAvailable)
                                            class="flex min-h-14 flex-col items-center justify-center rounded-md border border-line-default bg-surface-raised px-2 py-2 text-sm font-medium hover:border-line-strong disabled:cursor-not-allowed disabled:opacity-40 aria-pressed:border-accent-primary aria-pressed:bg-accent-tint">
                                            <span>{{ $region === null ? __('storefront.product.global') : ($regionNames[$region] ?? $region) }}</span>
                                            @unless ($regionAvailable)<span class="text-xs text-content-muted">{{ __('storefront.product.sold_out') }}</span>@endunless
                                        </button>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif

                        <fieldset>
                            <legend class="mb-3 text-lg font-semibold">{{ __('storefront.product.amount') }}</legend>
                            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                                @foreach ($variants as $variant)
                                    <button type="button" data-variant-option="{{ $variant['public_id'] }}" data-variant-region="{{ $variant['region'] ?? 'global' }}"
                                        data-price="{{ $variant['price']['formatted'] ?? '' }}" data-sku="{{ $variant['sku'] }}" data-amount="{{ $variant['price']['amount'] ?? '' }}" data-currency="{{ $variant['price']['currency'] ?? '' }}" data-title="{{ $variant['title'] }}"
                                        aria-pressed="{{ $selected && $variant['public_id'] === $selected['public_id'] ? 'true' : 'false' }}" @disabled(! $variant['purchasable'])
                                        @if ($variant['region'] !== $selectedRegion) hidden @endif
                                        class="flex min-h-[72px] flex-col items-center justify-center gap-0.5 rounded-md border border-line-default bg-surface-raised px-2 py-2 hover:border-line-strong disabled:cursor-not-allowed disabled:opacity-40 aria-pressed:border-accent-primary aria-pressed:bg-accent-tint">
                                        <span class="font-display text-lg font-bold" dir="ltr">{{ $variant['title'] }}</span>
                                        <span class="text-xs text-content-secondary">
                                            @if ($variant['purchasable'] && $variant['price'])
                                                {!! __('storefront.product.pay', ['amount' => '<bdi dir="ltr">'.e($variant['price']['formatted']).'</bdi>']) !!}
                                            @else
                                                {{ __('storefront.product.sold_out') }}
                                            @endif
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="variant_id" value="{{ $selected['purchasable'] ?? false ? $selected['public_id'] : '' }}" data-variant-input>
                            <p class="field-error" data-error-for="variant_id" hidden></p>
                        </fieldset>
                    @endif

                    <div class="panel flex flex-col gap-3 p-4 md:p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-display text-[44px] leading-[48px] font-bold" dir="ltr" data-variant-price aria-live="polite">{{ ($selected['purchasable'] ?? false) ? $selected['price']['formatted'] : '—' }}</p>
                            <span @class([
                                'rounded-xs px-2 py-0.5 text-xs font-medium',
                                'bg-status-success-tint text-status-success' => $availability === 'in_stock',
                                'bg-status-warning-tint text-status-warning' => $availability === 'limited',
                                'bg-status-danger-tint text-status-danger' => $availability === 'out_of_stock',
                            ])>{{ __('storefront.availability.'.$availability) }}</span>
                        </div>
                        <p class="text-sm leading-6 text-content-secondary">{{ __('storefront.product.tax_note') }}</p>
                        <p class="flex flex-wrap gap-x-5 gap-y-1 border-t border-line-subtle pt-3 text-xs">
                            <span class="inline-flex items-center gap-1.5"><x-store.icon name="{{ $instant ? 'zap' : 'clock' }}" :size="14" class="text-accent-text" /> {{ __('storefront.delivery.'.$product['delivery_type']) }}</span>
                            @if ($product['delivery_time_minutes'])
                                <span class="inline-flex items-center gap-1.5 text-content-secondary"><x-store.icon name="clock" :size="14" /> {{ __('storefront.product.delivery_time', ['minutes' => $product['delivery_time_minutes']]) }}</span>
                            @endif
                        </p>
                    </div>

                    @if ($product['delivery_fields'] !== [])
                        <fieldset class="flex flex-col gap-4">
                            <legend class="mb-1 text-lg font-semibold">{{ __('storefront.product.delivery_details') }}</legend>
                            @foreach ($product['delivery_fields'] as $field)
                                @php $fieldName = 'delivery_values['.$field['key'].']'; @endphp
                                <div>
                                    <label for="df-{{ $field['key'] }}" class="field-label">{{ $field['label'] }}@if ($field['required']) <span class="text-status-danger" aria-hidden="true">*</span>@endif</label>
                                    @if ($field['type'] === 'select')
                                        <select id="df-{{ $field['key'] }}" name="{{ $fieldName }}" class="field-input" @required($field['required']) aria-describedby="df-{{ $field['key'] }}-error">
                                            <option value="">{{ __('storefront.product.choose') }}</option>
                                            @foreach ($field['options'] ?? [] as $option)
                                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input id="df-{{ $field['key'] }}" name="{{ $fieldName }}" class="field-input" dir="ltr" autocomplete="off"
                                            type="{{ $field['type'] === 'email' ? 'email' : 'text' }}" @if ($field['type'] === 'number') inputmode="numeric" @endif
                                            @if ($field['max_length']) maxlength="{{ $field['max_length'] }}" @endif @required($field['required']) aria-describedby="df-{{ $field['key'] }}-error">
                                    @endif
                                    @if ($field['help_text'])<p class="field-help">{{ $field['help_text'] }}</p>@endif
                                    <p id="df-{{ $field['key'] }}-error" class="field-error" data-error-for="delivery_values.{{ $field['key'] }}" hidden></p>
                                </div>
                            @endforeach
                        </fieldset>
                    @endif

                    <div class="flex flex-col gap-3">
                        <div class="flex gap-3">
                            <div class="flex h-13 items-center rounded-md border border-line-default bg-surface-raised" data-stepper>
                                <button type="button" class="inline-flex size-11 items-center justify-center text-content-secondary hover:text-content-primary" data-step="-1"><x-store.icon name="minus" :size="16" :label="__('storefront.cart.decrease')" /></button>
                                <label for="quantity" class="sr-only">{{ __('storefront.cart.quantity') }}</label>
                                <input id="quantity" name="quantity" type="number" inputmode="numeric" min="1" max="{{ min(10, $product['max_quantity_per_order'] ?: 10) }}" value="1" class="w-10 bg-transparent text-center text-sm font-semibold [appearance:textfield] focus:outline-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" class="inline-flex size-11 items-center justify-center text-content-secondary hover:text-content-primary" data-step="1"><x-store.icon name="plus" :size="16" :label="__('storefront.cart.increase')" /></button>
                            </div>
                            <button type="submit" name="intent" value="add" class="btn-secondary btn-lg flex-1 whitespace-nowrap px-3" @disabled(! ($selected['purchasable'] ?? false)) data-add-button>
                                <x-store.icon name="bag" :size="18" /> {{ __('storefront.product.add') }}
                            </button>
                            <button type="button" class="btn-secondary btn-lg w-13 shrink-0 px-0 text-content-secondary aria-pressed:border-accent-ember aria-pressed:text-accent-ember-text" data-module="wishlist" data-slug="{{ $product['slug'] }}" aria-pressed="{{ $saved ? 'true' : 'false' }}">
                                <x-store.icon name="heart" :size="20" @class(['fill-current' => $saved]) />
                                <span class="sr-only" data-wishlist-label>{{ $saved ? __('account.wishlist.saved') : __('account.wishlist.add') }}</span>
                            </button>
                        </div>
                        <p class="field-error" data-error-for="quantity" hidden></p>
                        <button type="submit" name="intent" value="buy" class="btn-primary btn-lg w-full" @disabled(! ($selected['purchasable'] ?? false)) data-buy-button>
                            {{ __('storefront.product.buy_now') }} <x-store.icon name="arrow" :size="18" />
                        </button>
                        <div class="alert-danger" data-form-error hidden><x-store.icon name="alert" class="mt-0.5" /><p data-form-error-text></p></div>
                    </div>

                    @if ($paymentMethods !== [])
                        <div>
                            <p class="mb-2 text-xs text-content-muted">{{ __('storefront.product.pay_with') }}</p>
                            <ul class="flex flex-wrap gap-2">
                                @foreach ($paymentMethods as $method)
                                    <li class="rounded-xs border border-line-default px-2 py-0.5 font-mono text-xs text-content-secondary">{{ $paymentLabels[$method] ?? $method }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </form>
            </div>
