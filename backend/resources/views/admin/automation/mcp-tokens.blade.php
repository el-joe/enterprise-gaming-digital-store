{{-- MCP tokens (M15 over the M16 endpoints): list with revoke, and issue (read-only unless writes are allowed); the token is shown once. --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.automation.mcp-tokens'))
@section('heading', __('admin.nav.automation'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'mcp-tokens'])
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.automation.intro.mcp-tokens') }}</p>
    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <x-admin.table :table="$table" :title="__('admin.tabs.automation.mcp-tokens')" :actions="true" />
        <x-admin.card :title="__('admin.mcp.issue')" :subtitle="__('admin.mcp.issue_note')">
            <form class="flex flex-col gap-3" data-module="form" data-action="{{ url('/admin/api/mcp-tokens') }}" data-method="POST" data-success="reveal" data-reveal-target="#mcp-token-once" autocomplete="off" novalidate>
                <x-admin.field name="account" id="mt-account" :label="__('admin.mcp.account')" maxlength="120" :help="__('admin.mcp.account_help')" />
                <fieldset>
                    <legend class="field-label">{{ __('admin.mcp.scopes') }}</legend>
                    <div class="grid grid-cols-2 gap-1">
                        @foreach ($scopes as $scope)
                            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="scopes[]" value="{{ $scope }}" class="size-4 accent-(--color-accent-text)"> <span class="font-mono">{{ $scope }}</span></label>
                        @endforeach
                    </div>
                    <p class="field-error" data-error-for="scopes" hidden></p>
                </fieldset>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="allow_writes" class="size-4 accent-(--color-accent-text)"> {{ __('admin.mcp.allow_writes') }}</label>
                <x-admin.field name="expires_in_days" id="mt-days" type="number" :label="__('admin.mcp.expires_days')" value="90" />
                <x-admin.form-alerts />
                <div><button type="submit" class="admin-btn-primary">{{ __('admin.mcp.issue') }}</button></div>
            </form>
            <div class="mt-3">@include('admin.automation._reveal', ['id' => 'mcp-token-once', 'fields' => ['token' => __('admin.mcp.token')]])</div>
        </x-admin.card>
    </div>
@endsection
