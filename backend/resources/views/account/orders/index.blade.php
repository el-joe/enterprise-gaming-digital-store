@extends('layouts.account')

@section('title', __('account.orders.title'))
@section('nav', 'orders')

@section('page')
    <x-account.page-head :title="__('account.orders.title')" />

    <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-end gap-3" role="search" aria-label="{{ __('account.orders.filters') }}">
        <div class="w-full sm:w-56">
            <label for="order-status" class="field-label">{{ __('account.orders.status') }}</label>
            <select id="order-status" name="status" class="field-input">
                <option value="">{{ __('account.orders.all_statuses') }}</option>
                @foreach (\App\Domains\Orders\Enums\OrderStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case->value)>{{ __('storefront.order.status_value.'.$case->value) }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-48">
            <label for="order-period" class="field-label">{{ __('account.orders.period') }}</label>
            <select id="order-period" name="period" class="field-input">
                @foreach (\App\Http\Requests\Web\Account\OrderListPageRequest::PERIODS as $value)
                    <option value="{{ $value }}" @selected($period === $value)>{{ __('account.orders.periods.'.$value) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-secondary">{{ __('account.common.apply') }}</button>
    </form>

    @if ($orders === [])
        <x-store.empty-state art="no-orders" :title="__('account.orders.empty_title')" :text="$status || $period !== 'all' ? __('account.orders.empty_filtered') : __('account.orders.empty_text')" class="rounded-xl border border-line-subtle bg-surface-raised">
            <a href="{{ url()->localized('storefront.home') }}" class="btn-primary">{{ __('account.common.start_shopping') }}</a>
        </x-store.empty-state>
    @else
        @include('account.partials.orders-table', ['orders' => $orders])
        @include('account.partials.pagination', ['paginator' => $paginator])
    @endif
@endsection
