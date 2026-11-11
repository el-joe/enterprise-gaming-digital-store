@extends('layouts.admin')

@php($api = fn (string $path = ''): string => url('/admin/api/payment-gateways/'.$gateway->id.($path === '' ? '' : '/'.$path)))

@section('title', $gateway->name)
@section('heading', __('admin.nav.payments'))

@section('breadcrumbs')
    <x-admin.crumb :href="route('admin.payments.index')">{{ __('admin.payments.tab.gateways') }}</x-admin.crumb>
    <x-admin.crumb>{{ $gateway->name }}</x-admin.crumb>
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-3 md:mb-6">
        <h2 class="font-display text-2xl leading-8 font-bold">{{ $gateway->name }}</h2>
        <x-admin.badge :tone="$gateway->is_active ? 'success' : 'neutral'" :dot="false">{{ $gateway->is_active ? __('admin.common.active') : __('admin.common.inactive') }}</x-admin.badge>
        <x-admin.status group="mode" :value="$gateway->mode->value" :dot="false" />
    </div>

    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="flex min-w-0 flex-col gap-4 md:gap-6">
            <x-admin.card :title="__('admin.gateways.charge_title')">
                <p class="font-display text-[28px] leading-8 font-bold">{{ $gateway->chargeCurrency() }}</p>
                <p class="mt-1 text-sm text-content-secondary">{{ __('admin.gateways.charge_text') }}</p>
            </x-admin.card>

            <x-admin.card :title="__('admin.gateways.currencies')" :padded="false">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr><th class="admin-th">{{ __('admin.gateways.currency') }}</th><th class="admin-th">{{ __('admin.gateways.enabled') }}</th><th class="admin-th text-end">{{ __('admin.gateways.min') }}</th><th class="admin-th text-end">{{ __('admin.gateways.max') }}</th></tr></thead>
                        <tbody>
                            @forelse ($gateway->paymentGatewayCurrencies as $currency)
                                <tr><td class="admin-td font-mono text-xs">{{ $currency->currency }}</td><td class="admin-td"><x-admin.badge :tone="$currency->is_enabled ? 'success' : 'neutral'" :dot="false">{{ $currency->is_enabled ? __('admin.common.yes') : __('admin.common.no') }}</x-admin.badge></td><td class="admin-td text-end" dir="ltr">{{ $currency->min_amount === null ? '—' : rtrim(rtrim((string) $currency->min_amount, '0'), '.') }}</td><td class="admin-td text-end" dir="ltr">{{ $currency->max_amount === null ? '—' : rtrim(rtrim((string) $currency->max_amount, '0'), '.') }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="admin-td text-center text-content-muted">{{ __('admin.gateways.no_currencies') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>

            <x-admin.card :title="__('admin.gateways.rules')" :subtitle="__('admin.gateways.rules_note')" :padded="false">
                <div class="overflow-x-auto" data-module="action">
                    <table class="w-full">
                        <thead><tr>
                            <th class="admin-th">{{ __('admin.gateways.rule_effect') }}</th>
                            <th class="admin-th">{{ __('admin.gateways.rule_countries') }}</th>
                            <th class="admin-th">{{ __('admin.gateways.rule_currencies') }}</th>
                            <th class="admin-th">{{ __('admin.gateways.rule_limits') }}</th>
                            <th class="admin-th">{{ __('admin.gateways.enabled') }}</th>
                            <th class="admin-th"><span class="sr-only">{{ __('admin.common.actions') }}</span></th>
                        </tr></thead>
                        <tbody>
                            @forelse ($gateway->paymentEligibilityRules as $rule)
                                <tr>
                                    <td class="admin-td">{{ \App\DataTables\Cell::label('rule_effect', $rule->effect?->value) }} · {{ __('admin.gateways.priority', ['n' => $rule->priority]) }}</td>
                                    <td class="admin-td text-xs" dir="ltr">{{ implode(', ', $rule->countries ?? []) ?: __('admin.gateways.any') }}@if ($rule->ip_countries)<span class="block text-content-muted">IP: {{ implode(', ', $rule->ip_countries) }}</span>@endif</td>
                                    <td class="admin-td text-xs" dir="ltr">{{ implode(', ', $rule->currencies ?? []) ?: __('admin.gateways.any') }}</td>
                                    <td class="admin-td text-xs" dir="ltr">{{ $rule->min_amount_base === null ? '—' : rtrim(rtrim((string) $rule->min_amount_base, '0'), '.') }} – {{ $rule->max_amount_base === null ? '—' : rtrim(rtrim((string) $rule->max_amount_base, '0'), '.') }}@if ($rule->max_risk_level)<span class="block text-content-muted">{{ __('admin.gateways.max_risk', ['level' => $rule->max_risk_level->value]) }}</span>@endif</td>
                                    <td class="admin-td"><x-admin.badge :tone="$rule->is_active ? 'success' : 'neutral'" :dot="false">{{ $rule->is_active ? __('admin.common.yes') : __('admin.common.no') }}</x-admin.badge></td>
                                    <td class="admin-td text-end">
                                        @can('payments.manage')
                                            <span class="flex justify-end gap-1.5">
                                                <button type="button" @class(['admin-btn', 'text-status-danger' => $rule->is_active]) data-action-url="{{ url('/admin/api/payment-eligibility-rules/'.$rule->id) }}" data-action-method="PATCH" data-action-body='{"is_active":{{ $rule->is_active ? 'false' : 'true' }}}' data-confirm-title="{{ $rule->is_active ? __('admin.gateways.disable_rule') : __('admin.gateways.enable_rule') }}" @if ($rule->is_active) data-confirm-danger @endif>{{ $rule->is_active ? __('admin.gateways.disable') : __('admin.gateways.enable') }}</button>
                                                <button type="button" class="admin-btn text-status-danger" data-action-url="{{ url('/admin/api/payment-eligibility-rules/'.$rule->id) }}" data-action-method="DELETE" data-confirm-title="{{ __('admin.gateways.delete_rule') }}" data-confirm-danger>{{ __('admin.common.delete') }}</button>
                                            </span>
                                        @endcan
                                    </td>
                                </tr>
                                @can('payments.manage')
                                    <tr>
                                        <td colspan="6" class="admin-td pt-0">
                                            <details>
                                                <summary class="cursor-pointer text-sm font-medium text-accent-text">{{ __('admin.gateways.edit_rule') }}</summary>
                                                <div class="mt-3">@include('admin.payments._rule-form', ['rule' => $rule, 'action' => url('/admin/api/payment-eligibility-rules/'.$rule->id), 'method' => 'PUT', 'prefix' => 'rule-'.$rule->id])</div>
                                            </details>
                                        </td>
                                    </tr>
                                @endcan
                            @empty
                                <tr><td colspan="6" class="admin-td text-center text-content-muted">{{ __('admin.gateways.no_rules') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @can('payments.manage')
                    <details class="border-t border-line-subtle px-5 py-4">
                        <summary class="admin-btn inline-flex cursor-pointer"><x-store.icon name="plus" :size="16" /> {{ __('admin.gateways.add_rule') }}</summary>
                        <div class="mt-3">@include('admin.payments._rule-form', ['rule' => null, 'action' => url('/admin/api/payment-gateways/'.$gateway->id.'/eligibility-rules'), 'method' => 'POST', 'prefix' => 'rule-new'])</div>
                    </details>
                @endcan
            </x-admin.card>
        </div>

        <div class="flex flex-col gap-4 md:gap-6">
            @can('payments.manage')
                <x-admin.card :title="__('admin.gateways.status')">
                    <form class="flex flex-col gap-4" data-module="form" data-action="{{ $api() }}" data-method="PATCH" data-success="reload" data-confirm="{{ __('admin.gateways.status_confirm') }}" novalidate>
                        <label class="flex items-center justify-between gap-3 text-sm">
                            <span>{{ __('admin.common.active') }}</span>
                            <input type="checkbox" name="is_active" class="size-5 accent-(--color-accent-text)" @checked($gateway->is_active)>
                        </label>
                        <p class="field-error -mt-2" data-error-for="is_active" hidden></p>
                        <div>
                            <label for="g-mode" class="field-label">{{ __('admin.suppliers.col.mode') }}</label>
                            <select id="g-mode" name="mode" class="admin-input">
                                @foreach ($credentials as $mode => $state)<option value="{{ $mode }}" @selected($gateway->mode->value === $mode)>{{ \App\DataTables\Cell::label('mode', $mode) }}</option>@endforeach
                            </select>
                            <p class="field-error" data-error-for="mode" hidden></p>
                        </div>
                        <x-admin.form-alerts />
                        <div><button type="submit" class="admin-btn-primary">{{ __('admin.common.save') }}</button></div>
                    </form>
                </x-admin.card>
            @endcan

            @if ($public)
                <x-admin.card :title="__('admin.gateways.public_key')" :subtitle="__('admin.gateways.public_key_note')">
                    <ul class="flex flex-col gap-2 text-sm">
                        @foreach ($public['values'] as $mode => $value)
                            <li class="flex flex-col gap-0.5"><span class="text-content-muted">{{ \App\DataTables\Cell::label('mode', $mode) }} · {{ \App\DataTables\Cell::label('credential', $public['field']) }}</span><span class="font-mono text-xs break-all" dir="ltr">{{ $value ?? __('admin.common.not_configured') }}</span></li>
                        @endforeach
                    </ul>
                    @can('payments.credentials')
                        <form class="mt-3 flex flex-col gap-3" data-module="form" data-action="{{ $api('public-key') }}" data-method="PUT" data-success="reload" novalidate>
                            <div>
                                <label for="pk-mode" class="field-label">{{ __('admin.suppliers.col.mode') }}</label>
                                <select id="pk-mode" name="mode" class="admin-input">@foreach ($public['values'] as $mode => $value)<option value="{{ $mode }}" @selected($gateway->mode->value === $mode)>{{ \App\DataTables\Cell::label('mode', $mode) }}</option>@endforeach</select>
                            </div>
                            <x-admin.field name="value" id="pk-value" :label="\App\DataTables\Cell::label('credential', $public['field'])" dir="ltr" autocomplete="off" data-nullable :help="__('admin.gateways.public_key_help')" />
                            <x-admin.form-alerts />
                            <div><button type="submit" class="admin-btn">{{ __('admin.gateways.save_public_key') }}</button></div>
                        </form>
                    @endcan
                </x-admin.card>
            @endif

            <x-admin.card :title="__('admin.gateways.credentials')">
                <ul class="flex flex-col gap-2 text-sm">
                    @foreach ($credentials as $mode => $state)
                        <li class="flex items-center justify-between gap-3">
                            <span>{{ \App\DataTables\Cell::label('mode', $mode) }}@if ($state['rotated_at'])<span class="block text-xs text-content-muted">{{ __('admin.gateways.replaced', ['date' => \Illuminate\Support\Carbon::parse($state['rotated_at'])->format('Y-m-d H:i')]) }}</span>@endif</span>
                            <x-admin.badge :tone="$state['configured'] ? 'success' : 'neutral'" :dot="false">{{ $state['configured'] ? __('admin.common.configured') : __('admin.common.not_configured') }}</x-admin.badge>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-content-muted">{{ __('admin.common.write_only') }}</p>
                @can('payments.credentials')
                    @if ($fields !== [])
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm font-medium text-accent-text">{{ __('admin.common.replace_credentials') }}</summary>
                            <form class="mt-3 flex flex-col gap-3" data-module="form" data-action="{{ $api('credentials') }}" data-success="reload" data-confirm="{{ __('admin.common.replace_confirm') }}" autocomplete="off" novalidate>
                                <div>
                                    <label for="gc-mode" class="field-label">{{ __('admin.suppliers.col.mode') }}</label>
                                    <select id="gc-mode" name="mode" class="admin-input">@foreach ($credentials as $mode => $state)<option value="{{ $mode }}" @selected($gateway->mode->value === $mode)>{{ \App\DataTables\Cell::label('mode', $mode) }}</option>@endforeach</select>
                                    <p class="field-error" data-error-for="mode" hidden></p>
                                </div>
                                @foreach ($fields as $field)
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
@endsection
