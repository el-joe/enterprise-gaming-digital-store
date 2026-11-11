@extends('layouts.admin')

@section('title', __('admin.nav.suppliers'))
@section('heading', __('admin.nav.suppliers'))

@section('content')
    <p class="mb-4 text-sm text-content-secondary">{{ trans_choice('admin.suppliers.active_count', $summary['active'], ['count' => $summary['active']]) }}</p>
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
@endsection
