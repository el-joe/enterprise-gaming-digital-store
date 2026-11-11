{{-- One refund request (M15): the customer's reason, the order units to refund, approve through the refund flow or reject with a note. --}}
@extends('layouts.admin')

@php
    $open = $refundRequest->status?->value === 'requested';
    $api = url('/admin/api/refund-requests/'.$refundRequest->id);
@endphp

@section('title', __('admin.refund_requests.title', ['id' => $refundRequest->id]))
@section('heading', __('admin.nav.support'))

@section('breadcrumbs')
    <x-admin.crumb :href="route('admin.support.refund-requests.index')">{{ __('admin.tabs.support.refund-requests') }}</x-admin.crumb>
    <x-admin.crumb>#{{ $refundRequest->id }}</x-admin.crumb>
@endsection

@section('content')
    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <x-admin.card :title="__('admin.refund_requests.title', ['id' => $refundRequest->id])">
            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                <dt class="text-content-muted">{{ __('admin.col.status') }}</dt><dd><x-admin.status group="refund_request_status" :value="$refundRequest->status?->value" /></dd>
                <dt class="text-content-muted">{{ __('admin.col.order') }}</dt><dd class="font-mono text-xs">@can('orders.view')<a class="underline-offset-2 hover:underline" href="{{ route('admin.orders.show', $refundRequest->order?->public_id) }}">{{ $refundRequest->order?->order_number }}</a>@else{{ $refundRequest->order?->order_number }}@endcan</dd>
                <dt class="text-content-muted">{{ __('admin.col.customer') }}</dt><dd>{{ $refundRequest->user?->name ?? '—' }}</dd>
                <dt class="text-content-muted">{{ __('admin.col.created') }}</dt><dd dir="ltr">{{ $refundRequest->created_at?->format('Y-m-d H:i') }}</dd>
            </dl>
            <h3 class="mt-4 text-sm font-semibold">{{ __('admin.refund_requests.reason') }}</h3>
            <p class="mt-1 text-sm whitespace-pre-line text-content-secondary">{{ $refundRequest->reason }}</p>
        </x-admin.card>

        @if ($open)
            <div class="flex flex-col gap-4 md:gap-6">
                @can('refunds.create')
                    <x-admin.card :title="__('admin.refund_requests.approve')" :subtitle="__('admin.refund_requests.approve_note')">
                        <form class="flex flex-col gap-3" data-module="form" data-action="{{ $api.'/approve' }}" data-method="POST" data-success="reload" data-confirm="{{ __('admin.refund_requests.approve_confirm') }}" novalidate>
                            <fieldset>
                                <legend class="field-label">{{ __('admin.refund_requests.units') }}</legend>
                                <div class="flex flex-col gap-1">
                                    @foreach ($units as $unit)
                                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="unit_ids[]" value="{{ $unit->public_id }}" class="size-4 accent-(--color-accent-text)"> {{ $unit->orderItem?->name_snapshot }} <x-admin.status group="unit_status" :value="$unit->status?->value ?? $unit->status" /></label>
                                    @endforeach
                                </div>
                                <p class="field-error" data-error-for="unit_ids" hidden></p>
                            </fieldset>
                            <div>
                                <label for="rr-reason" class="field-label">{{ __('admin.refund_requests.refund_reason') }}</label>
                                <select id="rr-reason" name="reason" class="admin-input">@foreach (['customer_request', 'undelivered', 'fraud', 'admin'] as $reason)<option value="{{ $reason }}">{{ \App\DataTables\Cell::label('refund_reason', $reason) }}</option>@endforeach</select>
                                <p class="field-error" data-error-for="reason" hidden></p>
                            </div>
                            <div>
                                <label for="rr-destination" class="field-label">{{ __('admin.refund_requests.destination') }}</label>
                                <select id="rr-destination" name="destination" class="admin-input">@foreach (['original_method', 'wallet'] as $destination)<option value="{{ $destination }}">{{ \App\DataTables\Cell::label('refund_destination', $destination) }}</option>@endforeach</select>
                                <p class="field-error" data-error-for="destination" hidden></p>
                            </div>
                            <x-admin.field name="note" id="rr-note" :label="__('admin.common.note')" maxlength="2000" :help="__('admin.common.audited')" />
                            <x-admin.form-alerts />
                            <div><button type="submit" class="admin-btn-primary">{{ __('admin.refund_requests.approve') }}</button></div>
                        </form>
                    </x-admin.card>
                @endcan
                <x-admin.card :title="__('admin.refund_requests.reject')">
                    <form class="flex flex-col gap-3" data-module="form" data-action="{{ $api.'/reject' }}" data-method="POST" data-success="reload" data-confirm="{{ __('admin.refund_requests.reject_confirm') }}" data-confirm-danger novalidate>
                        <x-admin.field name="note" id="rj-note" :label="__('admin.common.note')" maxlength="2000" :help="__('admin.refund_requests.reject_help')" />
                        <x-admin.form-alerts />
                        <div><button type="submit" class="admin-btn-danger">{{ __('admin.refund_requests.reject') }}</button></div>
                    </form>
                </x-admin.card>
            </div>
        @endif
    </div>
@endsection
