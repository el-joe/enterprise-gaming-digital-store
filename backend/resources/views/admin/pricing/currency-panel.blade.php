{{-- Currency edit panel (M15): base currency, current rate and the fixed gateway charge currencies (OD-1, display only). --}}
<x-admin.card :title="__('admin.currency.context')">
    <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
        <dt class="text-content-muted">{{ __('admin.currency.base') }}</dt>
        <dd class="font-mono" dir="ltr">{{ $panel['base'] }}</dd>
        <dt class="text-content-muted">{{ __('admin.currency.rate') }}</dt>
        <dd class="font-mono" dir="ltr">{{ $panel['rate']?->effective_rate ?? '—' }}@if ($panel['rate']?->is_manual_active) <x-admin.badge tone="warning" :dot="false">{{ __('admin.currency.manual') }}</x-admin.badge>@endif</dd>
        <dt class="text-content-muted">{{ __('admin.currency.fetched') }}</dt>
        <dd dir="ltr">{{ $panel['rate']?->fetched_at?->format('Y-m-d H:i') ?? '—' }}</dd>
    </dl>
    <a href="{{ route('admin.pricing.exchange-rates.index') }}" class="mt-3 inline-flex text-sm font-medium text-accent-text">{{ __('admin.currency.manage_rates') }}</a>
</x-admin.card>
<x-admin.card :title="__('admin.currency.charge_title')" :subtitle="__('admin.currency.charge_note')">
    <ul class="flex flex-col gap-2 text-sm">
        @forelse ($panel['gateways'] as $gateway)
            <li class="flex items-center justify-between gap-3"><span>{{ $gateway->name }}</span><span class="font-mono" dir="ltr">{{ $gateway->chargeCurrency() }}</span></li>
        @empty
            <li class="text-content-muted">{{ __('admin.common.none') }}</li>
        @endforelse
    </ul>
</x-admin.card>
