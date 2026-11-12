@extends('layouts.storefront')

@section('title', __('storefront.checkout.title'))
@section('robots', 'noindex,nofollow')
@section('header', 'minimal')

@section('content')
    <div class="container-page pt-8 pb-16 md:pt-12">
        <h1 class="font-display text-[32px] leading-[38px] font-bold md:text-[40px] md:leading-[46px]">{{ __('storefront.checkout.title') }}</h1>
        @include('storefront.partials.steps', ['current' => 2])

        <form class="mt-8 grid gap-6 lg:grid-cols-[1fr_420px] lg:gap-8" data-module="checkout" novalidate
            data-options-url="{{ url('/api/v1/checkout/payment-options') }}"
            data-place-url="{{ url('/api/v1/checkout/place-order') }}"
            data-order-url="{{ url()->localized('storefront.orders.show', ['orderPublicId' => '__order__']) }}"
            data-return-url="{{ route('storefront.checkout.return', ['orderPublicId' => '__order__']) }}"
            data-summary-url="{{ url()->current() }}"
            @if ($stripeKey) data-stripe-key="{{ $stripeKey }}" @endif>

            <div class="flex flex-col gap-4">
                {{-- 1. Contact --}}
                <section class="keycut border border-line-subtle bg-surface-raised p-5 md:p-6" aria-labelledby="contact-title">
                    <h2 id="contact-title" class="flex items-center gap-3 text-xl font-semibold"><span class="inline-flex size-7 items-center justify-center rounded-full bg-surface-overlay text-xs">1</span>{{ __('storefront.checkout.contact') }}</h2>
                    @if ($guest)
                        <div class="mt-4">
                            <x-store.field name="email" type="email" :label="__('storefront.checkout.email')" :help="__('storefront.checkout.email_help')" autocomplete="email" required dir="ltr" />
                        </div>
                        <p class="mt-3 text-sm"><a href="{{ url()->localized('storefront.login') }}" class="text-accent-text hover:underline">{{ __('storefront.checkout.have_account') }}</a></p>
                    @else
                        <p class="mt-4 text-sm text-content-secondary">{{ __('storefront.checkout.signed_in_as') }} <span class="font-medium text-content-primary" dir="ltr">{{ $email }}</span></p>
                        <p class="mt-1 text-xs text-content-muted">{{ __('storefront.checkout.email_help') }}</p>
                    @endif
                </section>

                {{-- 2. Country and currency --}}
                <section class="keycut border border-line-subtle bg-surface-raised p-5 md:p-6" aria-labelledby="country-title">
                    <h2 id="country-title" class="flex items-center gap-3 text-xl font-semibold"><span class="inline-flex size-7 items-center justify-center rounded-full bg-surface-overlay text-xs">2</span>{{ __('storefront.checkout.country_currency') }}</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="checkout-country" class="field-label">{{ __('storefront.checkout.country') }}</label>
                            <select id="checkout-country" name="country" class="field-input" required data-country aria-describedby="checkout-country-error">
                                <option value="">{{ __('storefront.checkout.choose_country') }}</option>
                                @foreach ($countries as $countryOption)
                                    <option value="{{ $countryOption['code'] }}" @selected($countryOption['code'] === $country)>{{ $countryOption['name'] }}</option>
                                @endforeach
                            </select>
                            <p id="checkout-country-error" class="field-error" data-error-for="country" hidden></p>
                        </div>
                        <div data-module="currency">
                            <label for="checkout-currency" class="field-label">{{ __('storefront.checkout.display_currency') }}</label>
                            <select id="checkout-currency" class="field-input" data-currency-select>
                                @foreach ($currencies as $currencyOption)
                                    <option value="{{ $currencyOption['code'] }}" @selected($currencyOption['code'] === $cart['currency'])>{{ $currencyOption['code'] }} · {{ $currencyOption['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="alert-info mt-4" data-currency-notice hidden>
                        <x-store.icon name="info" class="mt-0.5 text-status-info" />
                        <div>
                            <p class="font-semibold">{{ __('storefront.checkout.currency_differs_title') }}</p>
                            <p class="text-content-secondary" data-currency-notice-text></p>
                        </div>
                    </div>
                </section>

                {{-- 3. Payment method --}}
                <section class="keycut border border-line-subtle bg-surface-raised p-5 md:p-6" aria-labelledby="payment-title">
                    <h2 id="payment-title" class="flex items-center gap-3 text-xl font-semibold"><span class="inline-flex size-7 items-center justify-center rounded-full bg-surface-overlay text-xs">3</span>{{ __('storefront.checkout.payment_method') }}</h2>
                    <div class="mt-4 flex flex-col gap-3" data-options role="radiogroup" aria-labelledby="payment-title" aria-busy="false">
                        <p class="text-sm text-content-secondary" data-options-placeholder>{{ __('storefront.checkout.choose_country_first') }}</p>
                    </div>
                    <p class="field-error" data-error-for="gateway" hidden></p>

                    <div class="mt-5 flex flex-col gap-3 border-t border-line-subtle pt-5">
                        <label class="flex items-start gap-3 text-sm">
                            <input type="checkbox" name="consents[terms]" value="1" class="mt-0.5 size-4 accent-(--color-accent-primary)" required data-terms>
                            <span>{!! __('storefront.checkout.terms', ['terms' => '<a href="'.e(url()->localized('storefront.pages.show', ['slug' => 'terms'])).'" class="text-accent-text underline" target="_blank" rel="noopener">'.e(__('storefront.footer.terms')).'</a>', 'refunds' => '<a href="'.e(url()->localized('storefront.pages.show', ['slug' => 'refund-policy'])).'" class="text-accent-text underline" target="_blank" rel="noopener">'.e(__('storefront.footer.refund_policy')).'</a>']) !!}</span>
                        </label>
                        <p class="field-error" data-error-for="consents.terms" hidden></p>
                        <label class="flex items-start gap-3 text-sm text-content-secondary">
                            <input type="checkbox" name="consents[marketing]" value="1" class="mt-0.5 size-4 accent-(--color-accent-primary)">
                            <span>{{ __('storefront.checkout.marketing') }}</span>
                        </label>
                    </div>

                    <div class="mt-5" data-stripe-panel hidden>
                        <p class="mb-3 text-sm font-semibold">{{ __('storefront.checkout.card_details') }}</p>
                        <div class="rounded-md border border-line-default bg-surface-sunken p-4" data-stripe-element></div>
                        <button type="button" class="btn-primary btn-lg mt-4 w-full" data-stripe-confirm>{{ __('storefront.checkout.confirm_card') }}</button>
                    </div>
                </section>
            </div>

            {{-- Summary --}}
            <aside class="flex flex-col gap-4 self-start lg:sticky lg:top-24">
                <div class="keycut border border-line-subtle bg-surface-raised p-5 md:p-6" data-fragment="summary" aria-labelledby="checkout-summary-title" data-module="cart" data-fragment-url="{{ url()->current() }}" data-fragment-name="summary">
                    @fragment('summary')
                        <h2 id="checkout-summary-title" class="text-xl font-semibold">{{ __('storefront.cart.summary') }}</h2>
                        <ul class="mt-4 flex flex-col gap-3">
                            @foreach ($cart['items'] as $item)
                                <li class="flex items-center gap-3">
                                    <x-store.product-image :slug="$item['product']['slug'] ?? ''" :name="$item['product']['name']" :image="($item['product']['image_source'] ?? null) === 'product' ? $item['product']['image'] : null" :logo="($item['product']['image_source'] ?? null) === 'brand' ? $item['product']['image'] : null" class="h-12 w-[72px] shrink-0" data-line-image />
                                    <p class="min-w-0 flex-1 text-sm font-medium">{{ $item['product']['name'] }} <span class="text-content-secondary" dir="ltr">· {{ $item['variant']['title'] }} × {{ $item['quantity'] }}</span>@if ($item['variant']['face_value'] ?? null)<span class="block text-xs text-content-muted" dir="ltr">{{ __('storefront.catalog.face_value') }}: {{ $item['variant']['face_value'] }}</span>@endif</p>
                                    <span class="text-sm font-semibold" dir="ltr">{{ $item['total']['formatted'] ?? '—' }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-4 border-t border-line-subtle pt-4">
                            @include('storefront.partials.coupon', ['coupon' => $cart['coupon']])
                        </div>
                        <dl class="mt-4 flex flex-col gap-2 text-sm">
                            <div class="flex justify-between"><dt class="text-content-secondary">{{ __('storefront.cart.subtotal_plain') }}</dt><dd dir="ltr">{{ $cart['totals']['subtotal']['formatted'] }}</dd></div>
                            @if ((float) $cart['totals']['discount']['amount'] > 0)
                                <div class="flex justify-between"><dt class="text-content-secondary">{{ __('storefront.cart.discount') }}</dt><dd class="text-status-success" dir="ltr">-{{ $cart['totals']['discount']['formatted'] }}</dd></div>
                            @endif
                            <div class="flex justify-between"><dt class="text-content-secondary">{{ __('storefront.cart.tax') }}</dt><dd class="text-content-secondary">{{ __('storefront.cart.tax_included') }}</dd></div>
                        </dl>
                        <div class="mt-4 flex items-end justify-between border-t border-line-subtle pt-4">
                            <span class="text-lg font-semibold">{{ __('storefront.cart.total') }}</span>
                            <span class="font-display text-[28px] leading-8 font-bold" dir="ltr" data-summary-total data-currency="{{ $cart['currency'] }}" data-amount="{{ $cart['totals']['total']['amount'] }}">{{ $cart['totals']['total']['formatted'] }}</span>
                        </div>
                    @endfragment
                </div>

                <div class="keycut border border-accent-primary/60 bg-field-volt p-5" data-charge-box hidden aria-live="polite">
                    <h2 class="flex items-center gap-2 text-lg font-semibold"><x-store.icon name="card" :size="18" class="text-accent-text" /> {{ __('storefront.checkout.charged_title') }}</h2>
                    <dl class="mt-3 flex flex-col divide-y divide-line-default/60 text-sm">
                        <div class="flex items-start justify-between gap-3 py-2.5">
                            <dt><span class="block font-medium">{{ __('storefront.checkout.display_price') }}</span><span class="text-xs text-content-secondary">{{ __('storefront.checkout.display_price_help') }}</span></dt>
                            <dd class="font-semibold" dir="ltr" data-charge-display></dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 py-2.5">
                            <dt><span class="block font-medium">{{ __('storefront.checkout.payment_amount') }}</span><span class="text-xs text-content-secondary">{{ __('storefront.checkout.payment_amount_help') }}</span></dt>
                            <dd class="font-display text-xl font-bold text-accent-text" dir="ltr" data-charge-payment></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <dt class="font-medium">{{ __('storefront.checkout.payment_currency') }}</dt>
                            <dd><span class="rounded-xs bg-accent-primary px-2 py-0.5 text-xs font-semibold text-content-on-accent" data-charge-currency></span></dd>
                        </div>
                    </dl>
                    <p class="mt-2 text-xs leading-5 text-content-secondary" data-charge-note></p>
                </div>

                <button type="submit" class="btn-primary btn-lg w-full" data-pay-button disabled>
                    <x-store.icon name="lock" :size="18" /> <span data-pay-label>{{ __('storefront.checkout.pay') }}</span>
                </button>
                <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5" /><p data-form-error-text></p></div>
                <p class="text-center text-xs text-content-muted">{{ __('storefront.checkout.verify_note') }}</p>
            </aside>
        </form>
    </div>
@endsection
