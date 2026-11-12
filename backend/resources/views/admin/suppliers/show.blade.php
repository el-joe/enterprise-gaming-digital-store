@extends('layouts.admin')

{{--
    Supplier detail (M14, Figma "Admin Supplier Detail" 20:3252 desktop / 20:3970 tablet): header
    with status badges and actions, capability matrix, health and fallback, write-only API
    credentials per mode, the Sandbox/Live switch, sync history, mappings and API logs.
--}}

@php
    $supplier = $detail['supplier'];
    $api = fn (string $path = ''): string => url('/admin/api/suppliers/'.$supplier->id.($path === '' ? '' : '/'.$path));
    $currentMode = $supplier->mode?->value ?? 'sandbox';
    $modeLabel = fn (string $mode): string => __('admin.suppliers.mode_label.'.$mode);
    $initials = \Illuminate\Support\Str::of($supplier->name)->explode(' ')->filter()->take(2)->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))->implode('');
    $lastSync = $detail['sync_runs']->first();
    $decimal = fn ($value): string => $value === null ? '' : rtrim(rtrim((string) $value, '0'), '.');
@endphp

@section('title', $supplier->name)
@section('heading', __('admin.suppliers.detail'))

@section('breadcrumbs')
    <x-admin.crumb :href="route('admin.suppliers.index')">{{ __('admin.nav.suppliers') }}</x-admin.crumb>
    <x-admin.crumb>{{ $supplier->name }}</x-admin.crumb>
@endsection

@section('content')
    {{-- Header: logo tile, name, adapter and note; status badges and actions. --}}
    <header class="mb-4 flex flex-col gap-4 md:mb-6 lg:flex-row lg:items-start lg:justify-between" data-module="action modal">
        <div class="flex min-w-0 items-center gap-3">
            <span class="keycut flex h-12 w-16 shrink-0 items-center justify-center bg-surface-sunken font-display text-lg font-bold text-content-secondary" aria-hidden="true">{{ $initials }}</span>
            <div class="min-w-0">
                <h2 class="font-display text-[22px] leading-[30px] font-semibold break-words">{{ $supplier->name }}</h2>
                <p class="text-[13px] leading-5 text-content-muted"><span class="font-mono" dir="ltr">{{ $supplier->adapter_key }}</span>@if ($supplier->adapter_key === 'reloadly_giftcards') · {{ __('admin.suppliers.awaiting_sandbox') }}@endif</p>
            </div>
        </div>
        <div class="flex flex-col gap-3 lg:items-end">
            <div class="flex flex-wrap items-center gap-2">
                <x-admin.badge :tone="$currentMode === 'production' ? 'success' : 'warning'" data-mode-badge>{{ $modeLabel($currentMode) }}</x-admin.badge>
                @if ($detail['circuit']['open'])
                    <x-admin.badge tone="danger">{{ __('admin.suppliers.circuit_open') }}</x-admin.badge>
                @else
                    <x-admin.status group="supplier_health" :value="$supplier->health?->value" />
                @endif
                <x-admin.status group="supplier_status" :value="$supplier->status?->value" :dot="false" />
                <x-admin.badge :dot="false">{{ __('admin.suppliers.balance', ['amount' => $detail['balance'] ?? '—']) }}</x-admin.badge>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('suppliers.manage')
                    <button type="button" class="admin-btn" data-action-url="{{ $api('test-connection') }}" data-confirm-title="{{ __('admin.suppliers.test_confirm') }}" data-confirm-label="{{ __('admin.suppliers.test') }}"><x-store.icon name="activity" :size="16" /> {{ __('admin.suppliers.test') }}</button>
                @endcan
                @can('suppliers.sync')
                    <div class="inline-flex" role="group" aria-label="{{ __('admin.suppliers.sync') }}">
                        <button type="button" class="admin-btn rounded-e-none" data-action-url="{{ $api('sync-catalog') }}" data-confirm-title="{{ __('admin.suppliers.sync_catalog_confirm') }}" data-no-reload>{{ __('admin.suppliers.sync_catalog') }}</button>
                        <button type="button" class="admin-btn -ms-px rounded-s-none" data-action-url="{{ $api('sync-pricing') }}" data-confirm-title="{{ __('admin.suppliers.sync_pricing_confirm') }}" data-no-reload>{{ __('admin.suppliers.sync_pricing') }}</button>
                    </div>
                @endcan
                @can('suppliers.manage')
                    <button type="button" class="admin-btn-primary" data-modal-open="supplier-edit" data-supplier-edit>{{ __('admin.common.edit') }}</button>
                @endcan
            </div>
        </div>
    </header>

    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        {{-- Capability matrix --}}
        <div class="flex min-w-0 flex-col gap-2">
            <h2 class="text-lg leading-[26px] font-semibold">{{ __('admin.suppliers.matrix') }}</h2>
            <p class="text-[13px] leading-5 text-content-muted">{{ __('admin.suppliers.matrix_note') }}</p>
            <div class="admin-card overflow-hidden">
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
            </div>
        </div>

        <div class="grid content-start gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-1">
            {{-- Health and fallback --}}
            <x-admin.card :title="__('admin.suppliers.health')" data-module="action">
                <dl class="flex flex-col gap-2.5 text-sm">
                    <div class="flex items-center justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.circuit') }}</dt><dd>@if ($detail['circuit']['open'])<x-admin.badge tone="danger" :dot="false">{{ __('admin.suppliers.open_until', ['date' => $detail['circuit']['until']->format('H:i')]) }}</x-admin.badge>@else<x-admin.badge tone="success" :dot="false">{{ __('admin.suppliers.closed') }}</x-admin.badge>@endif</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.last_sync') }}</dt><dd class="text-end font-semibold">{{ $lastSync ? \App\DataTables\Cell::label('sync_type', $lastSync->type?->value).' · '.($lastSync->started_at?->diffForHumans() ?? '—') : __('admin.suppliers.never') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.low_alert') }}</dt><dd class="font-semibold" dir="ltr">{{ $detail['low_balance_threshold'] ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.balance_at') }}</dt><dd>{{ $detail['balance_at']?->diffForHumans() ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.request_cap') }}</dt><dd dir="ltr">{{ $detail['per_request_cap'] ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.pending_verification') }}</dt><dd>{{ $detail['pending_verification'] }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-content-secondary">{{ __('admin.suppliers.fallback_supplier') }}</dt><dd class="text-end">{{ $supplier->fallback_enabled ? ($detail['fallback'] ?? __('admin.suppliers.fallback_any')) : __('admin.common.off') }}</dd></div>
                    <div class="flex items-center justify-between gap-3">
                        <dt id="fallback-label" class="text-content-secondary">{{ __('admin.suppliers.fallback_toggle') }}</dt>
                        <dd>
                            @can('suppliers.manage')
                                <button type="button" role="switch" aria-checked="{{ $supplier->fallback_enabled ? 'true' : 'false' }}" aria-labelledby="fallback-label"
                                    @class(['inline-flex h-6 w-11 items-center rounded-full p-0.5 transition', 'justify-end bg-accent-primary' => $supplier->fallback_enabled, 'justify-start border border-line-default bg-surface-sunken' => ! $supplier->fallback_enabled])
                                    data-action-url="{{ $api() }}" data-action-method="PATCH" data-action-body="{{ json_encode(['fallback_enabled' => ! $supplier->fallback_enabled]) }}"
                                    data-confirm-title="{{ $supplier->fallback_enabled ? __('admin.suppliers.fallback_off_confirm') : __('admin.suppliers.fallback_on_confirm') }}"><span class="size-5 rounded-full bg-content-primary shadow-card"></span></button>
                            @else
                                <x-admin.badge :tone="$supplier->fallback_enabled ? 'success' : 'neutral'" :dot="false">{{ $supplier->fallback_enabled ? __('admin.suppliers.on') : __('admin.common.off') }}</x-admin.badge>
                            @endcan
                        </dd>
                    </div>
                </dl>
                <p class="mt-3 text-xs text-content-muted">{{ __('admin.suppliers.ambiguous_note') }}</p>
                @can('suppliers.manage')
                    <button type="button" class="admin-btn mt-4 w-full" data-action-url="{{ $api('test-connection') }}" data-confirm-title="{{ __('admin.suppliers.test_confirm') }}" data-confirm-label="{{ __('admin.suppliers.test') }}"><x-store.icon name="activity" :size="16" /> {{ __('admin.suppliers.test') }}</button>
                @endcan
                @if ($detail['health_checks']->isNotEmpty())
                    <h3 class="mt-5 text-xs font-medium text-content-muted">{{ __('admin.suppliers.recent_checks') }}</h3>
                    <ul class="mt-2 flex flex-col gap-1 text-xs">
                        @foreach ($detail['health_checks'] as $check)
                            <li class="flex justify-between gap-2"><span dir="ltr">{{ $check->created_at?->format('Y-m-d H:i') }}</span><span @class(['text-status-success' => $check->reachable, 'text-status-danger' => ! $check->reachable])>{{ $check->reachable ? __('admin.suppliers.reachable', ['ms' => $check->latency_ms]) : __('admin.suppliers.unreachable') }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>

            {{-- API credentials and the Sandbox / Live switch --}}
            <x-admin.card :title="__('admin.suppliers.credentials')" data-module="modal">
                <div data-module="mode-switch" data-action="{{ $api() }}">
                    <p id="mode-switch-label" class="field-label">{{ __('admin.suppliers.active_mode') }}</p>
                    <div class="grid grid-cols-2 gap-1 rounded-sm border border-line-default bg-surface-sunken p-1" role="group" aria-labelledby="mode-switch-label" data-mode-switch>
                        @foreach (['sandbox', 'production'] as $mode)
                            @php($active = $currentMode === $mode)
                            @if (auth()->user()->can('suppliers.manage'))
                                <button type="button" aria-pressed="{{ $active ? 'true' : 'false' }}" data-mode-option data-mode="{{ $mode }}"
                                    @class(['min-h-8 rounded-xs px-3 text-sm font-medium transition', 'bg-surface-raised text-content-primary shadow-card' => $active, 'text-content-secondary hover:text-content-primary' => ! $active])
                                    data-confirm-title="{{ __('admin.suppliers.mode_to_'.$mode.'_title') }}" data-confirm-text="{{ __('admin.suppliers.mode_to_'.$mode.'_text') }}" data-confirm-label="{{ __('admin.suppliers.mode_to_'.$mode.'_confirm') }}" @if ($mode === 'production') data-confirm-danger @endif>
                                    <span @class(['me-1.5 inline-block size-1.5 rounded-full align-middle', 'bg-status-warning' => $mode === 'sandbox', 'bg-status-success' => $mode === 'production']) aria-hidden="true"></span>{{ $modeLabel($mode) }}
                                </button>
                            @else
                                <span @class(['min-h-8 rounded-xs px-3 py-1.5 text-center text-sm font-medium', 'bg-surface-raised text-content-primary' => $active, 'text-content-muted' => ! $active]) @if ($active) aria-current="true" @endif>{{ $modeLabel($mode) }}</span>
                            @endif
                        @endforeach
                    </div>
                    <p class="field-help">{{ __('admin.suppliers.active_mode_help') }}</p>
                    <p class="field-error" data-error-for="mode" hidden></p>
                    <x-admin.form-alerts />
                </div>

                <ul class="mt-4 flex flex-col divide-y divide-line-subtle border-t border-line-subtle text-sm">
                    @foreach ($detail['credentials'] as $mode => $configured)
                        <li class="flex items-center justify-between gap-3 py-3" data-credential-row="{{ $mode }}">
                            <div class="min-w-0">
                                <p class="font-medium">{{ $modeLabel($mode) }}@if ($mode === $currentMode) <span class="text-xs font-normal text-content-muted">· {{ __('admin.suppliers.current') }}</span>@endif</p>
                                <p class="text-xs text-content-muted">{{ isset($detail['credentials_rotated'][$mode]) ? __('admin.suppliers.rotated', ['time' => $detail['credentials_rotated'][$mode]->diffForHumans()]) : __('admin.suppliers.never_saved') }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <x-admin.badge :tone="$configured ? 'success' : 'neutral'" :dot="false">{{ $configured ? __('admin.common.configured') : __('admin.common.not_configured') }}</x-admin.badge>
                                @can('suppliers.credentials')
                                    @if ($detail['credential_fields'] !== [])
                                        <button type="button" class="admin-btn h-8 min-h-8" data-modal-open="credentials-{{ $mode }}">{{ __('admin.suppliers.replace') }}</button>
                                    @endif
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-1 text-xs text-content-muted">{{ __('admin.common.write_only') }}</p>
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

    @if ($logs)
        <div class="mt-6 mb-3 flex flex-wrap items-baseline justify-between gap-2">
            <h2 class="text-lg font-semibold">{{ __('admin.suppliers.logs') }}</h2>
            <p class="text-xs text-content-muted">{{ __('admin.suppliers.logs_note') }}</p>
        </div>
        <x-admin.table :table="$logs" :title="__('admin.suppliers.logs')" />
    @endif

    <h2 class="mt-6 mb-3 text-lg font-semibold">{{ __('admin.suppliers.mappings') }}</h2>
    <x-admin.table :table="$mappings" :title="__('admin.suppliers.mappings')" />

    {{-- Edit dialog: every field of SaveSupplierRequest except the adapter and mode (own control). --}}
    @can('suppliers.manage')
        <dialog id="supplier-edit" class="m-auto max-h-[calc(100dvh-2rem)] w-[min(640px,calc(100vw-2rem))] overflow-y-auto rounded-xl border border-line-subtle bg-surface-raised p-0 text-content-primary shadow-overlay backdrop:bg-ink-950/50" aria-labelledby="supplier-edit-title" data-module="modal">
            <form class="flex flex-col gap-4 p-6" data-module="form" data-action="{{ $api() }}" data-method="PATCH" data-success="reload" novalidate>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 id="supplier-edit-title" class="font-display text-xl font-bold">{{ __('admin.suppliers.edit_title') }}</h2>
                        <p class="mt-0.5 text-xs text-content-muted">{{ __('admin.suppliers.edit_note') }}</p>
                    </div>
                    <button type="button" class="admin-btn size-9 px-0" data-modal-close aria-label="{{ __('admin.common.cancel') }}"><x-store.icon name="x" :size="16" /></button>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2"><x-admin.field name="name" id="se-name" :label="__('admin.fields.name')" :value="$supplier->name" maxlength="120" required /></div>
                    <div>
                        <label for="se-status" class="field-label">{{ __('admin.suppliers.col.status') }}</label>
                        <select id="se-status" name="status" class="admin-input" aria-describedby="se-status-error">
                            @foreach (['active', 'inactive'] as $status)<option value="{{ $status }}" @selected($supplier->status?->value === $status)>{{ \App\DataTables\Cell::label('supplier_status', $status) }}</option>@endforeach
                        </select>
                        <p id="se-status-error" class="field-error" data-error-for="status" hidden></p>
                    </div>
                    <x-admin.field name="priority" id="se-priority" type="number" :label="__('admin.suppliers.col.priority')" :value="$supplier->priority" min="0" max="65535" :help="__('admin.suppliers.priority_help')" />
                </div>

                <fieldset class="grid gap-3 border-t border-line-subtle pt-4 sm:grid-cols-2">
                    <legend class="mb-2 text-sm font-semibold">{{ __('admin.suppliers.fallback') }}</legend>
                    <label class="flex items-center justify-between gap-3 text-sm sm:col-span-2">
                        <span>{{ __('admin.suppliers.fallback_toggle') }}</span>
                        <input type="hidden" name="fallback_enabled" value="0">
                        <input type="checkbox" name="fallback_enabled" value="1" class="size-5 accent-(--color-accent-text)" @checked($supplier->fallback_enabled)>
                    </label>
                    <div>
                        <label for="se-fallback" class="field-label">{{ __('admin.suppliers.fallback_supplier') }}</label>
                        <select id="se-fallback" name="fallback_supplier_id" class="admin-input" data-nullable aria-describedby="se-fallback-error">
                            <option value="">{{ __('admin.suppliers.fallback_any') }}</option>
                            @foreach ($detail['fallback_options'] as $id => $name)<option value="{{ $id }}" @selected($supplier->fallback_supplier_id === $id)>{{ $name }}</option>@endforeach
                        </select>
                        <p id="se-fallback-error" class="field-error" data-error-for="fallback_supplier_id" hidden></p>
                    </div>
                    <x-admin.field name="max_fallback_attempts" id="se-max-fallback" type="number" :label="__('admin.suppliers.max_fallback_attempts')" :value="$supplier->max_fallback_attempts" min="0" max="5" />
                </fieldset>

                <fieldset class="grid gap-3 border-t border-line-subtle pt-4 sm:grid-cols-2">
                    <legend class="mb-2 text-sm font-semibold">{{ __('admin.suppliers.limits') }}</legend>
                    <x-admin.field name="low_balance_threshold" id="se-low-balance" :label="__('admin.suppliers.low_alert')" :value="$decimal($supplier->low_balance_threshold)" inputmode="decimal" dir="ltr" data-nullable :help="__('admin.suppliers.in_currency', ['currency' => $supplier->balance_currency ?? '—'])" />
                    <x-admin.field name="per_request_cap_base" id="se-cap" :label="__('admin.suppliers.request_cap')" :value="$decimal($supplier->per_request_cap_base)" inputmode="decimal" dir="ltr" data-nullable :help="__('admin.suppliers.in_base_currency')" />
                    <x-admin.field name="verification_deadline_minutes" id="se-deadline" type="number" :label="__('admin.suppliers.verification_deadline')" :value="$supplier->verification_deadline_minutes" min="5" max="10080" />
                    <x-admin.field name="retry_policy[max_attempts]" id="se-retries" type="number" :label="__('admin.suppliers.retry_attempts')" :value="$supplier->retry_policy['max_attempts'] ?? 0" min="0" max="3" />
                    <x-admin.field name="connect_timeout_ms" id="se-connect" type="number" :label="__('admin.suppliers.connect_timeout')" :value="$supplier->connect_timeout_ms" min="1000" max="30000" />
                    <x-admin.field name="request_timeout_ms" id="se-request" type="number" :label="__('admin.suppliers.request_timeout')" :value="$supplier->request_timeout_ms" min="1000" max="60000" />
                    @if ($detail['base_url_override_allowed'])
                        <div class="sm:col-span-2"><x-admin.field name="base_url_override" id="se-base-url" type="url" :label="__('admin.suppliers.base_url_override')" :value="$supplier->base_url_override" dir="ltr" data-nullable :help="__('admin.suppliers.base_url_help')" /></div>
                    @endif
                </fieldset>

                <x-admin.form-alerts />
                <div class="flex justify-end gap-2">
                    <button type="button" class="admin-btn" data-modal-close>{{ __('admin.common.cancel') }}</button>
                    <button type="submit" class="admin-btn-primary">{{ __('admin.common.save') }}</button>
                </div>
            </form>
        </dialog>
    @endcan

    {{-- Credential dialogs, one per mode: write-only fields, never prefilled. --}}
    @can('suppliers.credentials')
        @if ($detail['credential_fields'] !== [])
            @foreach ($detail['credentials'] as $mode => $configured)
                <dialog id="credentials-{{ $mode }}" class="m-auto w-[min(480px,calc(100vw-2rem))] rounded-xl border border-line-subtle bg-surface-raised p-0 text-content-primary shadow-overlay backdrop:bg-ink-950/50" aria-labelledby="credentials-{{ $mode }}-title" data-module="modal">
                    <form class="flex flex-col gap-3 p-6" data-module="form" data-action="{{ $api('credentials') }}" data-success="reload" data-confirm="{{ __('admin.common.replace_confirm') }}" @if ($mode === 'production') data-confirm-danger @endif autocomplete="off" novalidate>
                        <div>
                            <h2 id="credentials-{{ $mode }}-title" class="font-display text-xl font-bold">{{ __('admin.suppliers.replace_title', ['mode' => $modeLabel($mode)]) }}</h2>
                            <p class="mt-0.5 text-xs text-content-muted">{{ __('admin.common.write_only') }}</p>
                        </div>
                        <input type="hidden" name="mode" value="{{ $mode }}">
                        <p class="field-error" data-error-for="mode" hidden></p>
                        @foreach ($detail['credential_fields'] as $field)
                            <x-admin.field name="credentials[{{ $field }}]" id="sc-{{ $mode }}-{{ $field }}" type="password" :label="\App\DataTables\Cell::label('credential', $field)" autocomplete="new-password" dir="ltr" />
                        @endforeach
                        <x-admin.field name="webhook_secret" id="sc-{{ $mode }}-webhook" type="password" :label="__('admin.common.webhook_secret')" autocomplete="new-password" dir="ltr" data-nullable :help="__('admin.common.optional')" />
                        <x-admin.form-alerts />
                        <div class="flex justify-end gap-2">
                            <button type="button" class="admin-btn" data-modal-close>{{ __('admin.common.cancel') }}</button>
                            <button type="submit" class="admin-btn-primary">{{ __('admin.common.save_credentials') }}</button>
                        </div>
                    </form>
                </dialog>
            @endforeach
        @endif
    @endcan
@endsection
