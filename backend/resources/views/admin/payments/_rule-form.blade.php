{{-- An eligibility rule form (M15): `rule` (null to create), `action`, `method`, `prefix` for ids. --}}
@php
    $list = fn ($values) => implode(', ', (array) ($values ?? []));
    $amount = fn ($value) => $value === null ? '' : rtrim(rtrim((string) $value, '0'), '.');
@endphp
<form class="grid gap-3 md:grid-cols-2" data-module="form" data-action="{{ $action }}" data-method="{{ $method }}" data-success="reload" novalidate>
    <div>
        <label for="{{ $prefix }}-effect" class="field-label">{{ __('admin.gateways.rule_effect') }}</label>
        <select id="{{ $prefix }}-effect" name="effect" class="admin-input">@foreach (['allow', 'deny'] as $effect)<option value="{{ $effect }}" @selected(($rule?->effect?->value ?? 'allow') === $effect)>{{ \App\DataTables\Cell::label('rule_effect', $effect) }}</option>@endforeach</select>
        <p class="field-error" data-error-for="effect" hidden></p>
    </div>
    <x-admin.field name="priority" type="number" :id="$prefix.'-priority'" :label="__('admin.fields.priority')" :value="$rule?->priority ?? 100" />
    <x-admin.field name="countries" :id="$prefix.'-countries'" :label="__('admin.gateways.rule_countries')" :value="$list($rule?->countries)" dir="ltr" data-csv :help="__('admin.gateways.csv_any')" />
    <x-admin.field name="ip_countries" :id="$prefix.'-ip'" :label="__('admin.gateways.rule_ip_countries')" :value="$list($rule?->ip_countries)" dir="ltr" data-csv :help="__('admin.gateways.csv_any')" />
    <x-admin.field name="currencies" :id="$prefix.'-currencies'" :label="__('admin.gateways.rule_currencies')" :value="$list($rule?->currencies)" dir="ltr" data-csv :help="__('admin.gateways.csv_any')" />
    <div>
        <label for="{{ $prefix }}-risk" class="field-label">{{ __('admin.gateways.rule_max_risk') }}</label>
        <select id="{{ $prefix }}-risk" name="max_risk_level" class="admin-input" data-nullable><option value="">{{ __('admin.gateways.any') }}</option>@foreach (['low', 'medium', 'high'] as $level)<option value="{{ $level }}" @selected($rule?->max_risk_level?->value === $level)>{{ \App\DataTables\Cell::label('risk_level', $level) }}</option>@endforeach</select>
    </div>
    <x-admin.field name="min_amount_base" :id="$prefix.'-min'" :label="__('admin.gateways.rule_min')" :value="$amount($rule?->min_amount_base)" inputmode="decimal" dir="ltr" data-nullable />
    <x-admin.field name="max_amount_base" :id="$prefix.'-max'" :label="__('admin.gateways.rule_max')" :value="$amount($rule?->max_amount_base)" inputmode="decimal" dir="ltr" data-nullable />
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="require_ip_match" class="size-4 accent-(--color-accent-text)" @checked($rule?->require_ip_match)> {{ __('admin.gateways.rule_ip_match') }}</label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="allow_guest" class="size-4 accent-(--color-accent-text)" @checked($rule?->allow_guest ?? true)> {{ __('admin.gateways.rule_guest') }}</label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" class="size-4 accent-(--color-accent-text)" @checked($rule?->is_active ?? true)> {{ __('admin.common.active') }}</label>
    <div class="md:col-span-2"><x-admin.form-alerts /></div>
    <div class="md:col-span-2"><button type="submit" class="admin-btn-primary">{{ $rule ? __('admin.common.save') : __('admin.gateways.add_rule') }}</button></div>
</form>
