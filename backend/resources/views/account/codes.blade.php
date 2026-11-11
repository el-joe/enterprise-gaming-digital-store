@extends('layouts.account')

@section('title', __('account.codes.title'))
@section('nav', 'codes')

@section('page')
    <x-account.page-head :title="__('account.codes.title')" :subtitle="__('account.codes.subtitle')" />

    <div class="alert-info">
        <x-store.icon name="info" class="mt-0.5 text-status-info" />
        <div>
            <p class="font-semibold">{{ __('account.codes.logged_title') }}</p>
            <p class="text-content-secondary">{{ __('account.codes.logged_text') }}</p>
        </div>
    </div>

    <form method="GET" action="{{ url()->current() }}" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_200px_auto] sm:items-end" role="search" aria-label="{{ __('account.codes.filters') }}">
        <div>
            <label for="codes-q" class="field-label">{{ __('account.codes.search') }}</label>
            <input id="codes-q" type="search" name="q" value="{{ $search }}" maxlength="100" class="field-input" placeholder="{{ __('account.codes.search_placeholder') }}">
        </div>
        <div>
            <label for="codes-state" class="field-label">{{ __('account.codes.state') }}</label>
            <select id="codes-state" name="state" class="field-input">
                <option value="">{{ __('account.codes.states.all') }}</option>
                @foreach (\App\Http\Requests\Web\Account\CodeListPageRequest::STATES as $value)
                    <option value="{{ $value }}" @selected($state === $value)>{{ __('account.codes.states.'.$value) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-secondary">{{ __('account.common.apply') }}</button>
    </form>

    @if ($units === [])
        <x-store.empty-state art="no-orders" :title="__('account.codes.empty_title')" :text="$state || $search ? __('account.codes.empty_filtered') : __('account.codes.empty_text')" class="rounded-xl border border-line-subtle bg-surface-raised" />
    @else
        <x-store.code-reveal class="flex flex-col gap-3">
            @foreach ($units as $unit)
                <article class="rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-5" aria-label="{{ $unit['name'] }}">
                    <div class="flex items-start gap-3">
                        <x-store.tile :slug="$unit['product_slug'] ?? ''" class="h-11 w-11 shrink-0 rounded-md" />
                        <div class="min-w-0 flex-1">
                            <h2 class="font-semibold">{{ $unit['name'] }}</h2>
                            <p class="text-xs text-content-muted">
                                <a href="{{ url()->localized('storefront.account.orders.show', ['orderPublicId' => $unit['order_public_id']]) }}" class="hover:text-content-primary">{{ __('account.codes.order') }} <span dir="ltr">{{ $unit['order_number'] }}</span></a>
                                @if ($unit['delivered_at']) · {{ __('account.codes.delivered') }} <x-account.time :at="$unit['delivered_at']" />@endif
                            </p>
                        </div>
                        @if ($unit['refunded'])
                            <x-account.badge tone="danger">{{ __('account.codes.states.refunded') }}</x-account.badge>
                        @elseif ($unit['first_revealed_at'])
                            <x-account.badge tone="accent">{{ __('account.codes.states.revealed') }}</x-account.badge>
                        @else
                            <x-account.badge tone="neutral">{{ __('account.codes.states.sealed') }}</x-account.badge>
                        @endif
                    </div>
                    @if ($unit['expires_at'])
                        <p class="mt-3"><x-account.badge tone="warning" :dot="false">{{ __('account.codes.expires') }} <x-account.time :at="$unit['expires_at']" /></x-account.badge></p>
                    @endif
                    <x-store.code-unit class="mt-3" :unit="$unit" :can-reveal="! $unit['refunded']"
                        :reveal-url="url('/api/v1/account/orders/'.$unit['order_public_id'].'/units/'.$unit['public_id'].'/reveal')" />
                    @if ($unit['refunded'])
                        <p class="mt-2 text-xs text-content-muted">{{ __('account.codes.refunded_note') }}</p>
                    @endif
                </article>
            @endforeach
        </x-store.code-reveal>
        @include('account.partials.pagination', ['paginator' => $paginator])
    @endif
@endsection
