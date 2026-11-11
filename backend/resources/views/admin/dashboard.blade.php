@extends('layouts.admin')

@section('title', __('admin.nav.dashboard'))
@section('heading', __('admin.nav.dashboard'))

@section('content')
    <div class="flex flex-col gap-4 md:gap-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <nav class="inline-flex rounded-md border border-line-default bg-surface-raised p-1" aria-label="{{ __('admin.dashboard.range') }}">
                @foreach ($ranges as $range)
                    <a href="{{ route('admin.dashboard', ['days' => $range]) }}" @class([
                        'rounded-sm px-3 py-1.5 text-sm font-medium',
                        'bg-accent-primary text-content-on-accent' => $range === $dashboard['days'],
                        'text-content-secondary hover:text-content-primary' => $range !== $dashboard['days'],
                    ]) @if ($range === $dashboard['days']) aria-current="true" @endif>{{ __('admin.dashboard.last_days', ['days' => $range]) }}</a>
                @endforeach
            </nav>
            <p class="text-xs text-content-muted">{{ __('admin.dashboard.basis', ['currency' => $dashboard['currency']]) }}</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 md:gap-4">
            @foreach ($dashboard['kpis'] as $kpi)
                <x-admin.kpi :label="__('admin.dashboard.kpi.'.$kpi['key'])" :value="$kpi['value']" :delta="$kpi['delta']" :good="$kpi['good']" :note="$kpi['note'] ?? null" :series="$kpi['series']" />
            @endforeach
        </div>

        <div @class(['grid gap-4 md:gap-6', 'lg:grid-cols-[1.4fr_1fr]' => $dashboard['profit_chart'] !== null])>
            @include('admin.partials.bar-chart', ['chart' => $dashboard['revenue_chart'], 'title' => __('admin.dashboard.revenue'), 'subtitle' => __('admin.dashboard.per_day', ['currency' => $dashboard['currency']]), 'tone' => 'fill-volt-500'])
            @if ($dashboard['profit_chart'] !== null)
                @include('admin.partials.bar-chart', ['chart' => $dashboard['profit_chart'], 'title' => __('admin.dashboard.profit'), 'subtitle' => __('admin.dashboard.per_day', ['currency' => $dashboard['currency']]), 'tone' => 'fill-ultra-400'])
            @endif
        </div>

        <div class="grid gap-4 md:gap-6 lg:grid-cols-2">
            @if ($dashboard['supplier_balances'] !== null)
                <x-admin.card :title="__('admin.dashboard.supplier_balances')" :subtitle="__('admin.dashboard.cached_balances')">
                    <ul class="flex flex-col divide-y divide-line-subtle">
                        @forelse ($dashboard['supplier_balances'] as $supplier)
                            <li class="flex items-start justify-between gap-3 py-2.5 first:pt-0 last:pb-0">
                                <div>
                                    <p class="font-medium">{{ $supplier['name'] }}</p>
                                    <p @class(['mt-0.5 text-xs', 'text-status-warning' => $supplier['low'], 'text-status-success' => ! $supplier['low'] && $supplier['known'], 'text-content-muted' => ! $supplier['known']])>
                                        {{ ! $supplier['known'] ? __('admin.dashboard.balance_unknown') : ($supplier['low'] ? __('admin.dashboard.balance_low') : __('admin.dashboard.balance_ok')) }}
                                    </p>
                                </div>
                                <div class="text-end">
                                    <p class="font-semibold" dir="ltr">{{ $supplier['balance'] ?? '—' }}</p>
                                    @if ($supplier['threshold'])<p class="mt-0.5 text-xs text-content-muted">{{ __('admin.dashboard.low_at', ['amount' => $supplier['threshold']]) }}</p>@endif
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-content-muted">{{ __('admin.dashboard.no_suppliers') }}</li>
                        @endforelse
                    </ul>
                </x-admin.card>
            @endif

            @if ($dashboard['gateways'] !== null)
                <x-admin.card :title="__('admin.dashboard.gateway_performance')" :subtitle="__('admin.dashboard.gateway_basis')">
                    <ul class="flex flex-col divide-y divide-line-subtle">
                        @forelse ($dashboard['gateways'] as $gateway)
                            <li class="flex items-start justify-between gap-3 py-2.5 first:pt-0 last:pb-0">
                                <div>
                                    <p class="font-medium">{{ $gateway['name'] }}</p>
                                    <p class="mt-0.5 text-xs text-content-muted">{{ __('admin.dashboard.charges_in', ['currency' => $gateway['currency']]) }}</p>
                                </div>
                                <div class="text-end">
                                    <p class="font-semibold" dir="ltr">{{ $gateway['rate'] ?? '—' }}</p>
                                    <p class="mt-0.5 text-xs text-content-muted">{{ trans_choice('admin.dashboard.attempts', $gateway['finished'], ['count' => $gateway['finished']]) }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-content-muted">{{ __('admin.dashboard.no_payments') }}</li>
                        @endforelse
                    </ul>
                </x-admin.card>
            @endif
        </div>

        <div class="grid gap-4 md:gap-6 lg:grid-cols-2">
            <x-admin.card :title="__('admin.dashboard.top_products')" :padded="false">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr><th class="admin-th">{{ __('admin.dashboard.product') }}</th><th class="admin-th text-end">{{ __('admin.dashboard.units') }}</th><th class="admin-th text-end">{{ __('admin.dashboard.revenue') }}</th></tr></thead>
                        <tbody>
                            @forelse ($dashboard['top_products'] as $product)
                                <tr><td class="admin-td">{{ $product['name'] }}</td><td class="admin-td text-end">{{ $product['units'] }}</td><td class="admin-td text-end" dir="ltr">{{ $product['revenue'] }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="admin-td text-center text-content-muted">{{ __('admin.dashboard.no_sales') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>

            @if ($dashboard['recent_orders'] !== null)
                <x-admin.card :title="__('admin.dashboard.recent_orders')" :padded="false">
                    <x-slot:actions><a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-accent-text hover:underline">{{ __('admin.common.view_all') }}</a></x-slot:actions>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead><tr><th class="admin-th">{{ __('admin.orders.col.number') }}</th><th class="admin-th">{{ __('admin.orders.col.status') }}</th><th class="admin-th text-end">{{ __('admin.orders.col.total') }}</th></tr></thead>
                            <tbody>
                                @forelse ($dashboard['recent_orders'] as $row)
                                    <tr>
                                        <td class="admin-td"><a href="{{ route('admin.orders.show', $row['order']) }}" class="font-mono text-xs hover:underline">{{ $row['number'] }}</a></td>
                                        <td class="admin-td"><x-admin.status group="order_status" :value="$row['status']" /></td>
                                        <td class="admin-td text-end font-semibold" dir="ltr">{{ $row['total'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="admin-td text-center text-content-muted">{{ __('admin.dashboard.no_orders') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.card>
            @endif
        </div>
    </div>
@endsection
