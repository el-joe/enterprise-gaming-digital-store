@extends('layouts.admin')

{{-- Supplier list (M14, Figma "Admin Suppliers" 20:2638): summary, KPIs, the table with mode and Open/Edit, and Add supplier. --}}

@section('title', __('admin.nav.suppliers'))
@section('heading', __('admin.nav.suppliers'))

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3" data-module="modal">
        <p class="text-sm text-content-secondary">{{ trans_choice('admin.suppliers.active_count', $summary['active'], ['count' => $summary['active']]) }}</p>
        @can('suppliers.manage')
            <button type="button" class="admin-btn-primary min-h-10 px-4" data-modal-open="supplier-create"><x-store.icon name="plus" :size="16" /> {{ __('admin.settings.add_supplier') }}</button>
        @endcan
    </div>
    <div class="mb-4 grid gap-3 sm:grid-cols-3 md:mb-6 md:gap-4">
        <x-admin.kpi :label="__('admin.suppliers.pending_verification')" :value="(string) $summary['pending_verification']" :note="__('admin.suppliers.pending_note')" good="down" />
        <x-admin.kpi :label="__('admin.suppliers.lowest_balance')" :value="$summary['lowest_balance'] ?? '—'" :note="__('admin.suppliers.cached')" />
        <x-admin.kpi :label="__('admin.suppliers.low_balance')" :value="(string) $summary['low_balance']" :note="__('admin.suppliers.low_note')" good="down" />
    </div>
    <x-admin.table :table="$table" :title="__('admin.nav.suppliers')" />
    <div class="alert-info mt-4 md:mt-6">
        <x-store.icon name="info" class="mt-0.5 text-status-info" />
        <div>
            <p class="font-semibold">{{ __('admin.suppliers.labelled_title') }}</p>
            <p class="text-content-secondary">{{ __('admin.suppliers.labelled_text') }}</p>
        </div>
    </div>

    @can('suppliers.manage')
        <dialog id="supplier-create" class="m-auto w-[min(520px,calc(100vw-2rem))] rounded-xl border border-line-subtle bg-surface-raised p-0 text-content-primary shadow-overlay backdrop:bg-ink-950/50" aria-labelledby="supplier-create-title" data-module="modal">
            <form class="grid gap-3 p-6 sm:grid-cols-2" data-module="form" data-action="{{ url('/admin/api/suppliers') }}" data-method="POST" data-success="reload" novalidate>
                <div class="sm:col-span-2">
                    <h2 id="supplier-create-title" class="font-display text-xl font-bold">{{ __('admin.settings.add_supplier') }}</h2>
                    <p class="mt-0.5 text-xs text-content-muted">{{ __('admin.suppliers.create_note') }}</p>
                </div>
                <div class="sm:col-span-2"><x-admin.field name="name" id="sn-name" :label="__('admin.fields.name')" maxlength="120" required /></div>
                <div>
                    <label for="sn-adapter" class="field-label">{{ __('admin.suppliers.col.adapter') }}</label>
                    <select id="sn-adapter" name="adapter_key" class="admin-input font-mono" dir="ltr" aria-describedby="sn-adapter-error">@foreach (array_keys((array) config('suppliers.adapters')) as $adapter)<option value="{{ $adapter }}">{{ $adapter }}</option>@endforeach</select>
                    <p id="sn-adapter-error" class="field-error" data-error-for="adapter_key" hidden></p>
                </div>
                <div>
                    <label for="sn-mode" class="field-label">{{ __('admin.suppliers.col.mode') }}</label>
                    <select id="sn-mode" name="mode" class="admin-input" aria-describedby="sn-mode-error">@foreach (['sandbox', 'production'] as $mode)<option value="{{ $mode }}">{{ __('admin.suppliers.mode_label.'.$mode) }}</option>@endforeach</select>
                    <p id="sn-mode-error" class="field-error" data-error-for="mode" hidden></p>
                </div>
                <x-admin.field name="priority" type="number" id="sn-priority" :label="__('admin.suppliers.col.priority')" value="100" min="0" max="65535" />
                <input type="hidden" name="status" value="inactive">
                <div class="sm:col-span-2"><x-admin.form-alerts /></div>
                <div class="flex justify-end gap-2 sm:col-span-2">
                    <button type="button" class="admin-btn" data-modal-close>{{ __('admin.common.cancel') }}</button>
                    <button type="submit" class="admin-btn-primary">{{ __('admin.settings.create_supplier') }}</button>
                </div>
            </form>
        </dialog>
    @endcan
@endsection
