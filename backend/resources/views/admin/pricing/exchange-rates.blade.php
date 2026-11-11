{{-- Exchange rates (M15, spec Phase 9.7): rates table with held and manual rates, "fetch now" and a manual override form. --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.pricing.exchange-rates'))
@section('heading', __('admin.nav.pricing'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'exchange-rates'])
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-content-secondary">{{ __('admin.rates.intro', ['base' => $base]) }}</p>
        @can('currency.manage')
            <div data-module="action">
                <button type="button" class="admin-btn" data-action-url="{{ url('/admin/api/exchange-rates/fetch-now') }}" data-confirm-title="{{ __('admin.rates.fetch_confirm') }}"><x-store.icon name="refresh" :size="16" /> {{ __('admin.rates.fetch') }}</button>
            </div>
        @endcan
    </div>
    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <x-admin.table :table="$table" :title="__('admin.tabs.pricing.exchange-rates')" :actions="true" />
        @can('currency.manage')
            <x-admin.card :title="__('admin.rates.override')" :subtitle="__('admin.rates.override_note')">
                <form class="flex flex-col gap-3" data-module="form" data-action="{{ url('/admin/api/exchange-rates/__currency__/override') }}" data-method="POST" data-success="reload" novalidate>
                    <div>
                        <label for="ov-currency" class="field-label">{{ __('admin.fields.currency') }}</label>
                        <select id="ov-currency" name="currency" class="admin-input">
                            @foreach ($currencies as $code => $label)
                                @if ($code !== $base)<option value="{{ $code }}">{{ $label }}</option>@endif
                            @endforeach
                        </select>
                    </div>
                    <x-admin.field name="rate" id="ov-rate" :label="__('admin.rates.rate', ['base' => $base])" inputmode="decimal" dir="ltr" />
                    <x-admin.field name="expires_at" id="ov-expires" type="datetime-local" :label="__('admin.rates.expires')" data-nullable :help="__('admin.common.optional')" />
                    <x-admin.field name="reason" id="ov-reason" :label="__('admin.common.reason')" maxlength="1000" :help="__('admin.common.audited')" />
                    <x-admin.form-alerts />
                    <div><button type="submit" class="admin-btn-primary">{{ __('admin.rates.apply') }}</button></div>
                </form>
            </x-admin.card>
        @endcan
    </div>
@endsection
