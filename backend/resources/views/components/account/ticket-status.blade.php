@props(['status'])

<x-account.badge :tone="match ($status) { 'pending_customer' => 'warning', 'resolved' => 'success', 'closed' => 'neutral', default => 'info' }" :dot="false" {{ $attributes }}>{{ __('account.tickets.statuses.'.$status) }}</x-account.badge>
