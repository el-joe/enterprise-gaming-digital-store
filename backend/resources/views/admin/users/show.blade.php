{{-- One staff member (M15): roles, explicit-grant permissions (Super Admin only), status and two-step reset. Nobody changes their own account here. --}}
@extends('layouts.admin')

@php
    $api = url('/admin/api/staff/'.$member->public_id);
    $memberRoles = $member->roles->pluck('name')->all();
    $memberPermissions = $member->permissions->pluck('name')->all();
@endphp

@section('title', $member->name)
@section('heading', __('admin.users.heading'))

@section('breadcrumbs')
    <x-admin.crumb :href="route('admin.users.staff.index')">{{ __('admin.tabs.users.staff') }}</x-admin.crumb>
    <x-admin.crumb>{{ $member->name }}</x-admin.crumb>
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-3 md:mb-6">
        <h2 class="font-display text-2xl leading-8 font-bold">{{ $member->name }}</h2>
        <x-admin.status group="user_status" :value="$member->status?->value" :dot="false" />
        <x-admin.badge :tone="$member->two_factor_confirmed_at ? 'success' : 'danger'" :dot="false">{{ __('admin.users.two_factor', ['state' => $member->two_factor_confirmed_at ? __('admin.users.on') : __('admin.users.off')]) }}</x-admin.badge>
        <span class="font-mono text-xs text-content-muted" dir="ltr">{{ $member->email }}</span>
    </div>

    @if ($isSelf)
        <div class="alert-warning mb-4" role="status"><x-store.icon name="info" class="mt-0.5" /><p>{{ __('admin.users.self_note') }}</p></div>
    @endif

    <div class="grid gap-4 md:gap-6 xl:grid-cols-2">
        <x-admin.card :title="__('admin.users.roles')" :subtitle="__('admin.users.roles_note')">
            <form class="flex flex-col gap-3" data-module="form" data-action="{{ $api.'/roles' }}" data-method="PUT" data-success="reload" data-confirm="{{ __('admin.users.roles_confirm') }}" novalidate>
                <div class="flex flex-col gap-1">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="size-4 accent-(--color-accent-text)" @checked(in_array($role->name, $memberRoles, true)) @disabled($isSelf || ($role->name === 'Super Admin' && ! $isSuperAdmin))>
                            {{ $role->name }}
                        </label>
                    @endforeach
                    <input type="hidden" name="roles[]" value="" data-list-sentinel>
                </div>
                <p class="field-error" data-error-for="roles" hidden></p>
                <x-admin.form-alerts />
                @unless ($isSelf)<div><button type="submit" class="admin-btn-primary">{{ __('admin.users.save_roles') }}</button></div>@endunless
            </form>
        </x-admin.card>

        <x-admin.card :title="__('admin.users.explicit')" :subtitle="__('admin.users.explicit_note')">
            <form class="flex flex-col gap-3" data-module="form" data-action="{{ $api.'/permissions' }}" data-method="PUT" data-success="reload" data-confirm="{{ __('admin.users.explicit_confirm') }}" data-confirm-danger novalidate>
                <div class="flex flex-col gap-1">
                    @foreach ($explicit as $permission)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->value }}" class="size-4 accent-(--color-accent-text)" @checked(in_array($permission->value, $memberPermissions, true)) @disabled($isSelf || ! $isSuperAdmin)>
                            <span class="font-mono text-xs">{{ $permission->value }}</span>
                        </label>
                    @endforeach
                    @foreach (array_diff($memberPermissions, array_map(fn ($p) => $p->value, $explicit)) as $other)
                        <input type="hidden" name="permissions[]" value="{{ $other }}">
                    @endforeach
                    <input type="hidden" name="permissions[]" value="" data-list-sentinel>
                </div>
                <p class="field-error" data-error-for="permissions" hidden></p>
                <x-admin.form-alerts />
                @if (! $isSelf && $isSuperAdmin)<div><button type="submit" class="admin-btn">{{ __('admin.users.save_permissions') }}</button></div>@endif
            </form>
        </x-admin.card>

        @unless ($isSelf)
            <x-admin.card :title="__('admin.users.account')">
                <div class="flex flex-wrap gap-2" data-module="action">
                    @if ($member->status?->value === 'active')
                        <button type="button" class="admin-btn-danger" data-action-url="{{ $api.'/status' }}" data-action-body='{"status":"suspended"}' data-confirm-title="{{ __('admin.users.suspend_confirm') }}" data-confirm-danger data-reason="{{ __('admin.common.reason') }}">{{ __('admin.users.suspend') }}</button>
                    @else
                        <button type="button" class="admin-btn" data-action-url="{{ $api.'/status' }}" data-action-body='{"status":"active"}' data-confirm-title="{{ __('admin.users.activate_confirm') }}" data-reason="{{ __('admin.common.reason') }}">{{ __('admin.users.activate') }}</button>
                    @endif
                    @if ($member->two_factor_confirmed_at)
                        <button type="button" class="admin-btn" data-action-url="{{ $api.'/reset-two-factor' }}" data-confirm-title="{{ __('admin.users.reset_2fa_confirm') }}" data-confirm-text="{{ __('admin.users.reset_2fa_text') }}" data-confirm-danger data-reason="{{ __('admin.common.reason') }}">{{ __('admin.users.reset_2fa') }}</button>
                    @endif
                </div>
            </x-admin.card>
        @endunless
    </div>
@endsection
