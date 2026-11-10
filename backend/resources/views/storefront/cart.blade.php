@extends('layouts.storefront')

@section('title', __('storefront.cart.title'))
@section('robots', 'noindex,nofollow')

@section('content')
    <div class="container-page pt-6 md:pt-10" data-module="cart" data-fragment-url="{{ url()->current() }}">
        <h1 class="font-display text-[32px] leading-[38px] font-bold md:text-[40px] md:leading-[46px]">{{ __('storefront.cart.title') }}</h1>

        <div data-fragment="cart" aria-live="polite">
            @fragment('cart')
                @php
                    $items = $cart['items'] ?? [];
                    $blocked = array_filter($items, static fn (array $item): bool => ! $item['purchasable']);
                    $units = array_sum(array_column($items, 'quantity'));
                @endphp

                @if ($items === [])
                    <x-store.empty-state art="empty-cart" :title="__('storefront.cart.empty_title')" :text="__('storefront.cart.empty_text')">
                        <a href="{{ url()->localized('storefront.home') }}" class="btn-primary mt-2">{{ __('storefront.cart.continue') }}</a>
                    </x-store.empty-state>
                @else
                    @if ($blocked !== [] || $cart['price_may_vary'])
                        <div class="alert-warning mt-6" role="alert">
                            <x-store.icon name="alert" class="mt-0.5 text-status-warning" />
                            <div>
                                <p class="font-semibold">{{ $blocked !== [] ? __('storefront.cart.blocked_title') : __('storefront.cart.stale_title') }}</p>
                                <p class="text-content-secondary">{{ $blocked !== [] ? __('storefront.cart.blocked_text') : __('storefront.cart.stale_text') }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_400px] lg:gap-8">
                        <ul class="flex flex-col gap-3">
                            @foreach ($items as $item)
                                <li class="card flex flex-col gap-4 p-3 sm:p-4" data-cart-item="{{ $item['id'] }}">
                                    <div class="flex gap-4">
                                        <x-store.tile :slug="$item['product']['slug'] ?? ''" class="h-16 w-24 shrink-0" />
                                        <div class="min-w-0 flex-1">
                                            <a href="{{ url()->localized('storefront.products.show', ['slug' => $item['product']['slug']]) }}" class="font-semibold hover:underline">{{ $item['product']['name'] }}</a>
                                            <p class="text-sm text-content-secondary">
                                                <span dir="ltr">{{ $item['variant']['title'] }}</span> · {{ __('storefront.delivery.'.$item['product']['delivery_type']) }}
                                            </p>
                                            @foreach ((array) $item['delivery_values'] as $key => $value)
                                                <p class="text-xs text-content-muted">{{ \Illuminate\Support\Str::headline($key) }}: <span dir="ltr" class="font-mono">{{ $value }}</span></p>
                                            @endforeach
                                            @unless ($item['purchasable'])
                                                <p class="mt-1 text-xs font-medium text-status-danger">{{ Lang::has('storefront.cart.block.'.$item['block_reason']) ? __('storefront.cart.block.'.$item['block_reason']) : __('storefront.cart.block.unavailable') }}</p>
                                            @endunless
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex h-10 items-center rounded-md border border-line-default" data-stepper>
                                            <button type="button" class="inline-flex size-10 items-center justify-center text-content-secondary hover:text-content-primary disabled:opacity-40" data-cart-quantity="{{ $item['quantity'] - 1 }}" @disabled($item['quantity'] <= 1)>
                                                <x-store.icon name="minus" :size="16" :label="__('storefront.cart.decrease')" />
                                            </button>
                                            <span class="w-8 text-center text-sm font-semibold" aria-label="{{ __('storefront.cart.quantity') }}">{{ $item['quantity'] }}</span>
                                            <button type="button" class="inline-flex size-10 items-center justify-center text-content-secondary hover:text-content-primary disabled:opacity-40" data-cart-quantity="{{ $item['quantity'] + 1 }}" @disabled($item['quantity'] >= 10)>
                                                <x-store.icon name="plus" :size="16" :label="__('storefront.cart.increase')" />
                                            </button>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="font-display text-base font-bold" dir="ltr">{{ $item['total']['formatted'] ?? '—' }}</span>
                                            <button type="button" class="inline-flex size-10 items-center justify-center rounded-md text-content-muted hover:text-status-danger" data-cart-remove>
                                                <x-store.icon name="trash" :size="18" :label="__('storefront.cart.remove', ['name' => $item['product']['name']])" />
                                            </button>
                                        </div>
                                    </div>
                                    <p class="field-error" data-error-for="items.{{ $item['id'] }}" hidden></p>
                                </li>
                            @endforeach
                        </ul>

                        <aside class="panel flex flex-col gap-4 self-start p-5 lg:sticky lg:top-24" aria-labelledby="summary-title">
                            <h2 id="summary-title" class="text-xl font-semibold">{{ __('storefront.cart.summary') }}</h2>
                            @include('storefront.partials.coupon', ['coupon' => $cart['coupon']])
                            <dl class="flex flex-col gap-2 border-t border-line-subtle pt-4 text-sm">
                                <div class="flex justify-between"><dt class="text-content-secondary">{{ trans_choice('storefront.cart.subtotal', $units, ['count' => $units]) }}</dt><dd dir="ltr">{{ $cart['totals']['subtotal']['formatted'] }}</dd></div>
                                @if ((float) $cart['totals']['discount']['amount'] > 0)
                                    <div class="flex justify-between"><dt class="text-content-secondary">{{ __('storefront.cart.discount') }}</dt><dd class="text-status-success" dir="ltr">-{{ $cart['totals']['discount']['formatted'] }}</dd></div>
                                @endif
                                <div class="flex justify-between"><dt class="text-content-secondary">{{ __('storefront.cart.tax') }}</dt><dd class="text-content-secondary">{{ __('storefront.cart.tax_included') }}</dd></div>
                            </dl>
                            <div class="flex items-end justify-between border-t border-line-subtle pt-4">
                                <span class="text-lg font-semibold">{{ __('storefront.cart.total') }}</span>
                                <span class="font-display text-[28px] leading-8 font-bold" dir="ltr">{{ $cart['totals']['total']['formatted'] }}</span>
                            </div>
                            <a href="{{ url()->localized('storefront.checkout') }}" @class(['btn-primary btn-lg w-full', 'pointer-events-none opacity-50' => ! $cart['checkout_ready']]) @unless ($cart['checkout_ready']) aria-disabled="true" @endunless>
                                {{ __('storefront.cart.checkout') }} <x-store.icon name="arrow" :size="18" />
                            </a>
                            <p class="text-center text-xs text-content-muted">{{ __('storefront.cart.currency_note') }}</p>
                        </aside>
                    </div>
                @endif
            @endfragment
        </div>
    </div>
@endsection
