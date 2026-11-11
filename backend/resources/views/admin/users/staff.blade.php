{{-- Staff list and invite (M15, Figma "Admin Users and Permissions"): two-step verification is required for every staff member. --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.users.staff'))
@section('heading', __('admin.users.heading'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'staff'])
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.users.intro') }}</p>
    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <x-admin.table :table="$table" :title="__('admin.tabs.users.staff')" />
        <x-admin.card :title="__('admin.users.invite')" :subtitle="__('admin.users.invite_note')">
            <form class="flex flex-col gap-3" data-module="form" data-action="{{ url('/admin/api/staff') }}" data-method="POST" data-success="reload" novalidate>
                <x-admin.field name="name" id="iv-name" :label="__('admin.fields.name')" maxlength="120" />
                <x-admin.field name="email" id="iv-email" type="email" :label="__('admin.customers.email')" maxlength="190" dir="ltr" />
                <fieldset>
                    <legend class="field-label">{{ __('admin.users.roles') }}</legend>
                    <div class="flex flex-col gap-1">
                        @foreach ($roles as $role)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="roles[]" value="{{ $role->name }}" class="size-4 accent-(--color-accent-text)"> {{ $role->name }}</label>
                        @endforeach
                    </div>
                    <p class="field-error" data-error-for="roles" hidden></p>
                </fieldset>
                <x-admin.form-alerts />
                <div><button type="submit" class="admin-btn-primary"><x-store.icon name="plus" :size="16" /> {{ __('admin.users.invite') }}</button></div>
            </form>
        </x-admin.card>
    </div>
@endsection
