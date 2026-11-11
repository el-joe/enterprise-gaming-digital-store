@extends('layouts.account')

@section('title', __('account.wishlist.title'))
@section('nav', 'wishlist')

@section('page')
    <x-account.page-head :title="__('account.wishlist.title')" :subtitle="trans_choice('account.wishlist.count', count($products), ['count' => count($products)])" />

    @if ($products === [])
        <x-store.empty-state art="empty-wishlist" :title="__('account.wishlist.empty_title')" :text="__('account.wishlist.empty_text')" class="rounded-xl border border-line-subtle bg-surface-raised">
            <a href="{{ url()->localized('storefront.home') }}" class="btn-primary">{{ __('account.common.start_shopping') }}</a>
        </x-store.empty-state>
    @else
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4">
            @foreach ($products as $product)
                <div class="relative" data-wishlist-card>
                    <x-store.product-card :product="$product" class="h-full" />
                    <button type="button" class="absolute end-2 top-2 z-10 inline-flex size-9 items-center justify-center rounded-full bg-surface-canvas/80 text-accent-ember-text hover:bg-surface-canvas"
                        data-module="wishlist" data-slug="{{ $product['slug'] }}" aria-pressed="true" data-remove-card>
                        <x-store.icon name="heart" :size="18" :label="__('account.wishlist.remove', ['name' => $product['name']])" />
                    </button>
                </div>
            @endforeach
        </div>
    @endif
@endsection
