{{-- Order status badge (Figma "Status Badge"): tone by status, label from storefront.order.status_value. --}}
@props(['status'])

@php
    $tone = match ($status) {
        'completed' => 'success',
        'paid', 'processing', 'supplier_pending', 'payment_processing' => 'info',
        'pending_payment', 'pending_verification', 'manual_review', 'partially_fulfilled', 'refund_pending' => 'warning',
        'failed' => 'danger',
        default => 'neutral',
    };
    $label = Lang::has('storefront.order.status_value.'.$status) ? __('storefront.order.status_value.'.$status) : \Illuminate\Support\Str::headline($status);
@endphp

<x-account.badge :tone="$tone" {{ $attributes }}>{{ $label }}</x-account.badge>
