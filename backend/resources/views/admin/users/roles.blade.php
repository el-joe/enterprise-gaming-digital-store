{{--
    Role permission matrix (M15, Figma "Role permissions (Spatie)"): one column per role, saved
    per role. Explicit-grant permissions (E) are never part of a role; the Super Admin role and
    roles the viewer holds cannot be edited here.
--}}
@extends('layouts.admin')

@php
    $viewer = auth()->user();
    $canEdit = $viewer->can('roles.manage');
@endphp

@section('title', __('admin.tabs.users.roles'))
@section('heading', __('admin.users.heading'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'roles'])
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <p class="max-w-3xl text-sm text-content-secondary">{{ __('admin.roles.intro') }}</p>
        @if ($canEdit)
            <form class="flex items-end gap-2" data-module="form" data-action="{{ url('/admin/api/roles') }}" data-method="POST" data-success="reload" novalidate>
                <x-admin.field name="name" id="role-new" :label="__('admin.roles.new')" maxlength="60" />
                <button type="submit" class="admin-btn-primary">{{ __('admin.common.create') }}</button>
            </form>
        @endif
    </div>

    <x-admin.card :title="__('admin.roles.matrix')" :subtitle="__('admin.roles.matrix_note')" :padded="false">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="admin-th">{{ __('admin.roles.permission') }}</th>
                        @foreach ($roles as $role)
                            @php($locked = ! $canEdit || $role->name === 'Super Admin' || $viewer->hasRole($role->name))
                            <th class="admin-th text-center align-top">
                                <span class="block whitespace-nowrap">{{ $role->name }}</span>
                                <span class="block text-xs font-normal text-content-muted">{{ trans_choice('admin.roles.members', $role->users_count, ['count' => $role->users_count]) }}</span>
                                @unless ($locked)
                                    <form id="role-{{ $role->id }}" class="mt-1" data-module="form" data-action="{{ url('/admin/api/roles/'.$role->id.'/permissions') }}" data-method="PUT" data-success="reload" data-confirm="{{ __('admin.roles.save_confirm', ['role' => $role->name]) }}" novalidate>
                                        <input type="hidden" name="permissions[]" value="" data-list-sentinel>
                                        <button type="submit" class="admin-btn h-7 px-2 text-xs">{{ __('admin.common.save') }}</button>
                                        <p class="field-error" data-error-for="permissions" hidden></p>
                                        <x-admin.form-alerts />
                                    </form>
                                @endunless
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                        @php($explicit = $permission->requiresExplicitGrant())
                        <tr>
                            <td class="admin-td font-mono text-xs whitespace-nowrap">{{ $permission->value }}@if ($explicit) <x-admin.badge tone="warning" :dot="false">E</x-admin.badge>@endif</td>
                            @foreach ($roles as $role)
                                @php($locked = ! $canEdit || $role->name === 'Super Admin' || $viewer->hasRole($role->name))
                                <td class="admin-td text-center">
                                    <input type="checkbox" class="size-4 accent-(--color-accent-text)" @if (! $locked) form="role-{{ $role->id }}" name="permissions[]" @endif value="{{ $permission->value }}" @checked($role->permissions->contains('name', $permission->value)) @disabled($locked || $explicit) aria-label="{{ $role->name }}: {{ $permission->value }}">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection
