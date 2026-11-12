{{--
    Settings (M15, Figma "Admin Settings" 21:6986): groups on the side, the group's fields, its
    write-only secrets (configured or not, replace or clear; never shown) and read-only values.
    Groups the viewer may not save show disabled controls. Related screens (payment
    eligibility, suppliers, app config) are linked or embedded here.
--}}
@extends('layouts.admin')

@php
    $viewer = auth()->user();
    $group = $screen['key'];
    $canSave = $viewer->can($screen['permission']);
    $canSecrets = $viewer->can('settings.manage_critical');
    $api = url('/admin/api/settings/'.$group);
@endphp

@section('title', __('admin.settings.group.'.$group))
@section('heading', __('admin.nav.settings'))

@section('content')
    <div class="grid gap-4 md:gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
        <nav class="min-w-0" aria-label="{{ __('admin.settings.groups') }}">
            <ul class="flex gap-1 overflow-x-auto lg:flex-col">
                @foreach ($groups as $item)
                    <li>
                        <a href="{{ route('admin.settings.show', $item) }}" @class([
                            'block rounded-md px-3 py-2 text-sm font-medium whitespace-nowrap',
                            'bg-surface-sunken text-content-primary' => $item === $group,
                            'text-content-secondary hover:bg-surface-sunken hover:text-content-primary' => $item !== $group,
                        ]) @if ($item === $group) aria-current="page" @endif>{{ __('admin.settings.group.'.$item) }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex min-w-0 flex-col gap-4 md:gap-6">
            @unless ($screen['applied'])
                <div class="alert-warning" role="status"><x-store.icon name="info" class="mt-0.5" /><p>{{ __('admin.settings.not_applied') }}</p></div>
            @endunless

            @if ($screen['readonly'] !== [])
                <x-admin.card :title="__('admin.settings.fixed')">
                    <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                        @foreach ($screen['readonly'] as $key => $value)
                            <dt class="text-content-muted">{{ __('admin.settings.field.'.$key) }}</dt>
                            <dd class="font-mono text-xs" dir="ltr">{{ $value }}</dd>
                        @endforeach
                    </dl>
                    <p class="mt-3 text-xs text-content-muted">{{ __('admin.settings.fixed_note.'.$group) }}</p>
                </x-admin.card>
            @endif

            @if ($screen['fields'] !== [])
                <x-admin.card :title="__('admin.settings.group.'.$group)" :subtitle="__('admin.settings.intro.'.$group)">
                    <form class="grid gap-4 md:grid-cols-2" data-module="form" data-action="{{ $api }}" data-method="PUT" data-success="reload" @if ($screen['permission'] === 'settings.manage_critical') data-confirm="{{ __('admin.settings.critical_confirm') }}" @endif novalidate>
                        @foreach ($screen['fields'] as $key => $field)
                            @php($id = 'st-'.$key)
                            <div @class(['md:col-span-2' => $field['type'] === 'json'])>
                                @if ($field['type'] === 'bool')
                                    <label class="flex min-h-10 items-center gap-3 text-sm"><input id="{{ $id }}" type="checkbox" name="{{ $key }}" class="size-5 accent-(--color-accent-text)" @checked($field['value']) @disabled(! $canSave)> {{ __('admin.settings.field.'.$key) }}</label>
                                @else
                                    <label for="{{ $id }}" class="field-label">{{ __('admin.settings.field.'.$key) }}</label>
                                    @if ($field['type'] === 'select')
                                        <select id="{{ $id }}" name="{{ $key }}" class="admin-input" @disabled(! $canSave)>
                                            @foreach ($field['options'] as $option)<option value="{{ $option }}" @selected((string) $field['value'] === $option)>{{ __('admin.settings.option.'.$option) }}</option>@endforeach
                                        </select>
                                    @elseif ($field['type'] === 'json')
                                        <textarea id="{{ $id }}" name="{{ $key }}" rows="4" class="admin-input py-2 font-mono text-xs" dir="ltr" data-json @disabled(! $canSave)>{{ $field['value'] === null || $field['value'] === [] ? '' : json_encode($field['value'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</textarea>
                                    @else
                                        <input id="{{ $id }}" name="{{ $key }}" value="{{ $field['value'] }}" class="admin-input" @if (in_array($field['type'], ['int', 'decimal'], true)) inputmode="decimal" dir="ltr" @endif data-nullable @disabled(! $canSave)>
                                    @endif
                                @endif
                                @if (\Illuminate\Support\Facades\Lang::has('admin.settings.help.'.$key))<p class="field-help">{{ __('admin.settings.help.'.$key) }}</p>@endif
                                <p class="field-error" data-error-for="{{ $key }}" hidden></p>
                            </div>
                        @endforeach
                        <div class="md:col-span-2"><x-admin.form-alerts /></div>
                        @if ($canSave)
                            <div class="flex justify-end md:col-span-2"><button type="submit" class="admin-btn-primary min-h-10 px-5">{{ __('admin.common.save') }}</button></div>
                        @else
                            <p class="text-sm text-content-muted md:col-span-2">{{ __('admin.settings.needs', ['permission' => $screen['permission']]) }}</p>
                        @endif
                    </form>
                </x-admin.card>
            @endif

            @foreach ($screen['secrets'] as $key => $configured)
                <x-admin.card :title="__('admin.settings.field.'.$key)">
                    <p class="flex items-center justify-between gap-3 text-sm">
                        <span>{{ __('admin.settings.secret_state') }}</span>
                        <x-admin.badge :tone="$configured ? 'success' : 'neutral'" :dot="false">{{ $configured ? __('admin.common.configured') : __('admin.common.not_configured') }}</x-admin.badge>
                    </p>
                    <p class="mt-2 text-xs text-content-muted">{{ __('admin.common.write_only') }}</p>
                    @if ($canSecrets && in_array($key, $screen['uploads'], true))
                        {{-- A JSON key file (FCM service account): pasted or uploaded, sent once, never shown again. --}}
                        <form class="mt-3 flex flex-col gap-3" data-module="form" data-multipart data-action="{{ $api.'/secrets/'.$key }}" data-method="PUT" data-success="reload" data-confirm="{{ __('admin.common.replace_confirm') }}" autocomplete="off" novalidate>
                            <div>
                                <label for="sec-{{ $key }}" class="field-label">{{ __('admin.settings.paste_json') }}</label>
                                <textarea id="sec-{{ $key }}" name="value" rows="4" class="admin-input py-2 font-mono text-xs" dir="ltr" autocomplete="off" spellcheck="false"></textarea>
                                <p class="field-error" data-error-for="value" hidden></p>
                            </div>
                            <div>
                                <label for="sec-file-{{ $key }}" class="field-label">{{ __('admin.settings.upload_json') }}</label>
                                <input id="sec-file-{{ $key }}" type="file" name="file" accept="application/json,.json" class="admin-input py-2 text-sm">
                                <p class="field-help">{{ __('admin.settings.help.'.$key) }}</p>
                                <p class="field-error" data-error-for="file" hidden></p>
                            </div>
                            <x-admin.form-alerts />
                            <div class="flex flex-wrap gap-2"><button type="submit" class="admin-btn">{{ __('admin.settings.replace') }}</button></div>
                        </form>
                        @if ($configured)
                            <form class="mt-2" data-module="form" data-action="{{ $api.'/secrets/'.$key }}" data-method="PUT" data-success="reload" data-confirm="{{ __('admin.settings.clear_confirm') }}" data-confirm-danger novalidate>
                                <input type="hidden" name="value" value="" data-nullable>
                                <x-admin.form-alerts />
                                <button type="submit" class="admin-btn">{{ __('admin.settings.clear') }}</button>
                            </form>
                        @endif
                    @elseif ($canSecrets)
                        <form class="mt-3 flex flex-col gap-3" data-module="form" data-action="{{ $api.'/secrets/'.$key }}" data-method="PUT" data-success="reload" data-confirm="{{ __('admin.common.replace_confirm') }}" autocomplete="off" novalidate>
                            <x-admin.field name="value" :id="'sec-'.$key" type="password" :label="__('admin.settings.new_value')" autocomplete="new-password" dir="ltr" :help="__('admin.settings.clear_help')" />
                            <x-admin.form-alerts />
                            <div><button type="submit" class="admin-btn">{{ __('admin.settings.replace') }}</button></div>
                        </form>
                    @endif
                </x-admin.card>
            @endforeach

            @if ($group === 'analytics')
                @php($searchConsole = app(\App\Domains\Analytics\Services\AnalyticsSettings::class))
                <x-admin.card :title="__('admin.search_console.connection')" :subtitle="__('admin.search_console.connect_help')">
                    @if (session('search_console_status'))<div class="alert-success mb-3" role="status"><x-store.icon name="check" class="mt-0.5" /><p>{{ session('search_console_status') }}</p></div>@endif
                    @if (session('search_console_error'))<div class="alert-danger mb-3" role="alert"><x-store.icon name="alert" class="mt-0.5" /><p>{{ session('search_console_error') }}</p></div>@endif
                    <p class="flex items-center justify-between gap-3 text-sm">
                        <span>{{ __('admin.search_console.title') }}</span>
                        <x-admin.badge :tone="$searchConsole->searchConsoleConnected() ? 'success' : 'neutral'" :dot="false">{{ $searchConsole->searchConsoleConnected() ? __('admin.search_console.connected') : __('admin.search_console.not_connected') }}</x-admin.badge>
                    </p>
                    <p class="mt-3 text-xs text-content-muted">{{ __('admin.search_console.redirect_uri') }}</p>
                    <p class="font-mono text-xs break-all" dir="ltr">{{ route('admin.content.search-console.callback') }}</p>
                    <div class="mt-4 flex flex-wrap gap-2" data-module="action">
                        @if ($canSecrets)
                            <a href="{{ route('admin.content.search-console.connect') }}" class="admin-btn-primary">{{ $searchConsole->searchConsoleConnected() ? __('admin.search_console.reconnect') : __('admin.search_console.connect') }}</a>
                            @if ($searchConsole->searchConsoleConnected())
                                <button type="button" class="admin-btn" data-action-url="{{ url('/admin/api/seo/search-console/connection') }}" data-action-method="DELETE" data-confirm-title="{{ __('admin.search_console.disconnect_confirm') }}" data-confirm-danger>{{ __('admin.search_console.disconnect') }}</button>
                            @endif
                        @endif
                        @can('seo.manage')<a href="{{ route('admin.content.search-console') }}" class="admin-btn">{{ __('admin.search_console.report') }}</a>@endcan
                    </div>
                </x-admin.card>
            @endif

            @if ($group === 'checkout')
                <x-admin.card :title="__('admin.settings.payment_rules')">
                    <p class="text-sm text-content-secondary">{{ __('admin.settings.payment_rules_text') }}</p>
                    @can('payments.view')<a href="{{ route('admin.payments.index') }}" class="mt-3 inline-flex text-sm font-medium text-accent-text">{{ __('admin.tabs.payments.gateways') }}</a>@endcan
                </x-admin.card>
            @endif

            @if ($group === 'suppliers' && $viewer->can('suppliers.manage'))
                <x-admin.card :title="__('admin.settings.add_supplier')" :subtitle="__('admin.settings.add_supplier_note')">
                    <form class="grid gap-3 md:grid-cols-2" data-module="form" data-action="{{ url('/admin/api/suppliers') }}" data-method="POST" data-success="redirect" data-redirect="{{ route('admin.suppliers.index') }}" novalidate>
                        <x-admin.field name="name" id="sp-name" :label="__('admin.fields.name')" maxlength="120" />
                        <div>
                            <label for="sp-adapter" class="field-label">{{ __('admin.suppliers.col.adapter') }}</label>
                            <select id="sp-adapter" name="adapter_key" class="admin-input">@foreach (array_keys((array) config('suppliers.adapters')) as $adapter)<option value="{{ $adapter }}">{{ $adapter }}</option>@endforeach</select>
                            <p class="field-error" data-error-for="adapter_key" hidden></p>
                        </div>
                        <x-admin.field name="priority" type="number" id="sp-priority" :label="__('admin.fields.priority')" value="100" />
                        <div>
                            <label for="sp-mode" class="field-label">{{ __('admin.suppliers.col.mode') }}</label>
                            <select id="sp-mode" name="mode" class="admin-input"><option value="sandbox">{{ \App\DataTables\Cell::label('mode', 'sandbox') }}</option><option value="production">{{ \App\DataTables\Cell::label('mode', 'production') }}</option></select>
                        </div>
                        <input type="hidden" name="status" value="inactive">
                        <div class="md:col-span-2"><x-admin.form-alerts /></div>
                        <div class="md:col-span-2"><button type="submit" class="admin-btn-primary">{{ __('admin.settings.create_supplier') }}</button></div>
                    </form>
                </x-admin.card>
            @endif
        </div>
    </div>
@endsection
