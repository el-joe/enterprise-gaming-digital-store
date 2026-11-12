@extends('layouts.admin')

@php
    $supplier = $detail['supplier'];
    $api = fn (string $path = ''): string => url('/admin/api/suppliers/'.$supplier->id.($path === '' ? '' : '/'.$path));
    $user = auth()->user();
@endphp

@section('title', $supplier->name)
@section('heading', __('admin.suppliers.detail'))

@section('breadcrumbs')
    <x-admin.crumb :href="route('admin.suppliers.index')">{{ __('admin.nav.suppliers') }}</x-admin.crumb>
    <x-admin.crumb>{{ $supplier->name }}</x-admin.crumb>
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3 md:mb-6">
        <div>
            <h2 class="font-display text-2xl leading-8 font-bold">{{ $supplier->name }}</h2>
            <p class="mt-1 text-sm text-content-secondary"><span class="font-mono">{{ $supplier->adapter_key }}</span> · <x-admin.status group="mode" :value="$supplier->mode?->value" :dot="false" />
                @if ($supplier->adapter_key === 'reloadly_giftcards') · {{ __('admin.suppliers.awaiting_sandbox') }} @endif</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($detail['circuit']['open'])
                <x-admin.badge tone="danger">{{ __('admin.suppliers.circuit_open') }}</x-admin.badge>
            @else
                <x-admin.status group="supplier_health" :value="$supplier->health?->value" />
            @endif
            <x-admin.status group="supplier_status" :value="$supplier->status?->value" />
            <x-admin.badge :dot="false">{{ __('admin.suppliers.balance', ['amount' => $detail['balance'] ?? '—']) }}</x-admin.badge>
        </div>
    </div>

    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <x-admin.card :title="__('admin.suppliers.matrix')" :subtitle="__('admin.suppliers.matrix_note')" :padded="false">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead><tr>
                        <th class="admin-th">{{ __('admin.suppliers.capability') }}</th>
                        @foreach ($detail['matrix_columns'] as $column)
                            <th @class(['admin-th', 'text-content-primary' => $column['current']]) @if ($column['current']) aria-current="true" @endif>{{ $column['name'] }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody>
                        @foreach ($detail['matrix'] as $capability => $levels)
                            <tr>
                                <th scope="row" class="admin-td text-start font-mono text-xs font-normal text-content-secondary">{{ $capability }}</th>
                                @foreach ($detail['matrix_columns'] as $column)
                                    @php($level = $levels[$column['id']] ?? 'none')
                                    <td @class(['admin-td', 'bg-surface-canvas' => $column['current']])>
                                        <span @class([
                                            'inline-flex items-center gap-1.5 text-xs font-medium',
                                            'text-status-success' => $level === 'full',
                                            'text-status-warning' => $level === 'advisory',
                                            'text-status-info' => $level === 'unverified',
                                            'text-content-muted' => $level === 'none',
                                        ])><x-store.icon :name="match ($level) { 'full' => 'check', 'advisory' => 'info', 'unverified' => 'help', default => 'x' }" :size="14" />{{ \App\DataTables\Cell::label('capability', $level) }}</span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        <div class="flex flex-col gap-4 md:gap-6" data-module="action">
            <x-admin.card :title="__('admin.suppliers.health')">
                <dl class="flex flex-col gap-2.5 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.circuit') }}</dt><dd>@if ($detail['circuit']['open'])<x-admin.badge tone="danger" :dot="false">{{ __('admin.suppliers.open_until', ['date' => $detail['circuit']['until']->format('H:i')]) }}</x-admin.badge>@else<x-admin.badge tone="success" :dot="false">{{ __('admin.suppliers.closed') }}</x-admin.badge>@endif</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.balance_at') }}</dt><dd>{{ $detail['balance_at']?->diffForHumans() ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.low_alert') }}</dt><dd dir="ltr">{{ $detail['low_balance_threshold'] ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.request_cap') }}</dt><dd dir="ltr">{{ $detail['per_request_cap'] ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.pending_verification') }}</dt><dd>{{ $detail['pending_verification'] }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.fallback') }}</dt><dd>{{ $supplier->fallback_enabled ? ($detail['fallback'] ?? __('admin.suppliers.fallback_any')) : __('admin.common.off') }}</dd></div>
                </dl>
                <p class="mt-3 text-xs text-content-muted">{{ __('admin.suppliers.ambiguous_note') }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @can('suppliers.manage')
                        <button type="button" class="admin-btn" data-action-url="{{ $api('test-connection') }}" data-confirm-title="{{ __('admin.suppliers.test_confirm') }}" data-confirm-label="{{ __('admin.suppliers.test') }}"><x-store.icon name="activity" :size="16" /> {{ __('admin.suppliers.test') }}</button>
                    @endcan
                    @can('suppliers.sync')
                        <button type="button" class="admin-btn" data-action-url="{{ $api('sync-catalog') }}" data-confirm-title="{{ __('admin.suppliers.sync_catalog_confirm') }}" data-no-reload>{{ __('admin.suppliers.sync_catalog') }}</button>
                        <button type="button" class="admin-btn" data-action-url="{{ $api('sync-pricing') }}" data-confirm-title="{{ __('admin.suppliers.sync_pricing_confirm') }}" data-no-reload>{{ __('admin.suppliers.sync_pricing') }}</button>
                    @endcan
                </div>
                @if ($detail['health_checks']->isNotEmpty())
                    <h3 class="mt-5 text-xs font-medium text-content-muted">{{ __('admin.suppliers.recent_checks') }}</h3>
                    <ul class="mt-2 flex flex-col gap-1 text-xs">
                        @foreach ($detail['health_checks'] as $check)
                            <li class="flex justify-between gap-2"><span dir="ltr">{{ $check->created_at?->format('Y-m-d H:i') }}</span><span @class(['text-status-success' => $check->reachable, 'text-status-danger' => ! $check->reachable])>{{ $check->reachable ? __('admin.suppliers.reachable', ['ms' => $check->latency_ms]) : __('admin.suppliers.unreachable') }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>

            @can('suppliers.manage')
                <x-admin.card :title="__('admin.suppliers.settings')">
                    <form class="flex flex-col gap-4" data-module="form" data-action="{{ $api() }}" data-method="PATCH" data-success="reload" novalidate>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="s-status" class="field-label">{{ __('admin.suppliers.col.status') }}</label>
                                <select id="s-status" name="status" class="admin-input">
                                    @foreach (['active', 'inactive'] as $status)<option value="{{ $status }}" @selected($supplier->status?->value === $status)>{{ \App\DataTables\Cell::label('supplier_status', $status) }}</option>@endforeach
                                </select>
                                <p class="field-error" data-error-for="status" hidden></p>
                            </div>
                            <x-admin.field name="priority" type="number" :label="__('admin.suppliers.col.priority')" :value="$supplier->priority" min="0" />
                        </div>
                        <label class="flex items-center justify-between gap-3 text-sm">
                            <span>{{ __('admin.suppliers.fallback_toggle') }}</span>
                            <input type="hidden" name="fallback_enabled" value="0">
                            <input type="checkbox" name="fallback_enabled" class="size-5 accent-(--color-accent-text)" @checked($supplier->fallback_enabled)>
                        </label>
                        <div>
                            <label for="s-fallback" class="field-label">{{ __('admin.suppliers.fallback_supplier') }}</label>
                            <select id="s-fallback" name="fallback_supplier_id" class="admin-input" data-nullable>
                                <option value="">{{ __('admin.suppliers.fallback_any') }}</option>
                                @foreach ($detail['fallback_options'] as $id => $name)<option value="{{ $id }}" @selected($supplier->fallback_supplier_id === $id)>{{ $name }}</option>@endforeach
                            </select>
                            <p class="field-error" data-error-for="fallback_supplier_id" hidden></p>
                        </div>
                        <x-admin.field name="low_balance_threshold" :label="__('admin.suppliers.low_alert')" :value="$supplier->low_balance_threshold === null ? '' : rtrim(rtrim((string) $supplier->low_balance_threshold, '0'), '.')" inputmode="decimal" dir="ltr" data-nullable :help="__('admin.suppliers.in_currency', ['currency' => $supplier->balance_currency ?? '—'])" />
                        <x-admin.form-alerts />
                        <div><button type="submit" class="admin-btn-primary">{{ __('admin.common.save') }}</button></div>
                    </form>
                </x-admin.card>
            @endcan

            <x-admin.card :title="__('admin.suppliers.credentials')">
                <ul class="flex flex-col gap-2 text-sm">
                    @foreach ($detail['credentials'] as $mode => $configured)
                        <li class="flex justify-between gap-3"><span>{{ \App\DataTables\Cell::label('mode', $mode) }}</span>
                            <x-admin.badge :tone="$configured ? 'success' : 'neutral'" :dot="false">{{ $configured ? __('admin.common.configured') : __('admin.common.not_configured') }}</x-admin.badge></li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-content-muted">{{ __('admin.common.write_only') }}</p>
                @can('suppliers.manage')
                    {{-- Active mode switch: calls go to the sandbox or production API with that mode's credentials. --}}
                    <form class="mt-4 flex flex-col gap-3 border-t border-line-subtle pt-4" data-module="form" data-action="{{ $api() }}" data-method="PATCH" data-success="reload" data-confirm="{{ __('admin.suppliers.mode_confirm') }}" novalidate>
                        <div>
                            <label for="s-active-mode" class="field-label">{{ __('admin.suppliers.active_mode') }}</label>
                            <select id="s-active-mode" name="mode" class="admin-input">@foreach ($detail['credentials'] as $mode => $configured)<option value="{{ $mode }}" @selected($supplier->mode?->value === $mode)>{{ \App\DataTables\Cell::label('mode', $mode) }}{{ $configured ? '' : ' · '.__('admin.common.not_configured') }}</option>@endforeach</select>
                            <p class="field-help">{{ __('admin.suppliers.active_mode_help') }}</p>
                            <p class="field-error" data-error-for="mode" hidden></p>
                        </div>
                        <x-admin.form-alerts />
                        <div><button type="submit" class="admin-btn">{{ __('admin.suppliers.switch_mode') }}</button></div>
                    </form>
                @endcan
                @can('suppliers.credentials')
                    @if ($detail['credential_fields'] !== [])
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm font-medium text-accent-text">{{ __('admin.common.replace_credentials') }}</summary>
                            <form class="mt-3 flex flex-col gap-3" data-module="form" data-action="{{ $api('credentials') }}" data-success="reload" data-confirm="{{ __('admin.common.replace_confirm') }}" autocomplete="off" novalidate>
                                <div>
                                    <label for="sc-mode" class="field-label">{{ __('admin.suppliers.col.mode') }}</label>
                                    <select id="sc-mode" name="mode" class="admin-input">@foreach ($detail['credentials'] as $mode => $configured)<option value="{{ $mode }}" @selected($supplier->mode?->value === $mode)>{{ \App\DataTables\Cell::label('mode', $mode) }}</option>@endforeach</select>
                                    <p class="field-error" data-error-for="mode" hidden></p>
                                </div>
                                @foreach ($detail['credential_fields'] as $field)
                                    <x-admin.field name="credentials[{{ $field }}]" type="password" :label="\App\DataTables\Cell::label('credential', $field)" autocomplete="new-password" dir="ltr" />
                                @endforeach
                                <x-admin.field name="webhook_secret" type="password" :label="__('admin.common.webhook_secret')" autocomplete="new-password" dir="ltr" data-nullable :help="__('admin.common.optional')" />
                                <x-admin.form-alerts />
                                <div><button type="submit" class="admin-btn-primary">{{ __('admin.common.save_credentials') }}</button></div>
                            </form>
                        </details>
                    @endif
                @endcan
            </x-admin.card>
        </div>
    </div>

    @if ($detail['sync_runs']->isNotEmpty())
        <x-admin.card :title="__('admin.suppliers.sync_history')" :padded="false" class="mt-4 md:mt-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead><tr><th class="admin-th">{{ __('admin.suppliers.sync_type') }}</th><th class="admin-th">{{ __('admin.suppliers.col.status') }}</th><th class="admin-th text-end">{{ __('admin.suppliers.sync_items') }}</th><th class="admin-th">{{ __('admin.suppliers.sync_started') }}</th></tr></thead>
                    <tbody>
                        @foreach ($detail['sync_runs'] as $run)
                            <tr>
                                <td class="admin-td">{{ \App\DataTables\Cell::label('sync_type', $run->type?->value) }}</td>
                                <td class="admin-td">{{ \App\DataTables\Cell::label('sync_status', $run->status?->value) }}</td>
                                <td class="admin-td text-end">{{ $run->items_seen }} / {{ $run->items_failed }}</td>
                                <td class="admin-td" dir="ltr">{{ $run->started_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @endif

    <h2 class="mt-6 mb-3 text-lg font-semibold">{{ __('admin.suppliers.mappings') }}</h2>
    <x-admin.table :table="$mappings" :title="__('admin.suppliers.mappings')" />

    @if ($logs)
        <h2 class="mt-6 mb-1 text-lg font-semibold">{{ __('admin.suppliers.logs') }}</h2>
        <p class="mb-3 text-xs text-content-muted">{{ __('admin.suppliers.logs_note') }}</p>
        <x-admin.table :table="$logs" :title="__('admin.suppliers.logs')" />
    @endif
@endsection
