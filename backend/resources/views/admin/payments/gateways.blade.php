@extends('layouts.admin')

@section('title', __('admin.nav.payments'))
@section('heading', __('admin.nav.payments'))

@section('content')
    @include('admin.payments._tabs', ['current' => 'gateways'])

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3" data-module="action">
        <p class="text-sm text-content-secondary">{{ __('admin.gateways.intro') }}</p>
        @can('payments.manage')
            <button type="button" class="admin-btn" data-action-url="{{ url('/admin/api/payments/reconcile') }}" data-confirm-title="{{ __('admin.gateways.reconcile_confirm') }}" data-confirm-text="{{ __('admin.gateways.reconcile_text') }}" data-confirm-label="{{ __('admin.gateways.reconcile') }}"><x-store.icon name="refresh" :size="16" /> {{ __('admin.gateways.reconcile') }}</button>
        @endcan
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($gateways as $gateway)
            @php($state = $credentials[$gateway->id])
            <section class="admin-card flex flex-col gap-3 p-4" aria-labelledby="gw-{{ $gateway->id }}">
                <div class="flex items-start justify-between gap-2">
                    <h2 id="gw-{{ $gateway->id }}" class="text-lg font-semibold">{{ $gateway->name }}</h2>
                    <div class="flex flex-wrap justify-end gap-1.5">
                        <x-admin.badge :tone="$gateway->is_active ? 'success' : 'neutral'" :dot="false">{{ $gateway->is_active ? __('admin.common.active') : __('admin.common.inactive') }}</x-admin.badge>
                        <x-admin.status group="mode" :value="$gateway->mode->value" :dot="false" />
                    </div>
                </div>
                <dl class="flex flex-col gap-1.5 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.gateways.charges') }}</dt><dd class="font-semibold">{{ $gateway->chargeCurrency() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.gateways.credentials') }}</dt><dd>{{ $state[$gateway->mode->value]['configured'] ? __('admin.common.configured') : __('admin.common.not_configured') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.gateways.currencies') }}</dt><dd dir="ltr">{{ $gateway->paymentGatewayCurrencies->where('is_enabled', true)->pluck('currency')->implode(', ') ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.gateways.rules') }}</dt><dd>{{ $gateway->getAttribute('active_rules_count') }}</dd></div>
                </dl>
                @if (in_array($gateway->adapter_key, ['stripe', 'paypal', 'paymob'], true))
                    <p class="text-xs text-status-warning">{{ __('admin.gateways.sandbox_pending') }}</p>
                @endif
                <a href="{{ route('admin.payments.gateways.show', $gateway) }}" class="admin-btn mt-auto w-full">{{ __('admin.gateways.configure') }}</a>
            </section>
        @empty
            <p class="text-sm text-content-muted">{{ __('admin.gateways.none') }}</p>
        @endforelse
    </div>
@endsection
