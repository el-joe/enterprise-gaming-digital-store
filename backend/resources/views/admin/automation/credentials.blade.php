{{-- n8n automation (M15 over the M17 endpoints): credentials with revoke and issue (token and signing secret shown once), and the workflow switches. --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.automation.automation-credentials'))
@section('heading', __('admin.nav.automation'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'automation-credentials'])
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.automation.intro.automation-credentials') }}</p>
    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <x-admin.table :table="$table" :title="__('admin.automation.credentials')" :actions="true" />
        <x-admin.card :title="__('admin.automation.issue')" :subtitle="__('admin.automation.issue_note')">
            <form class="flex flex-col gap-3" data-module="form" data-action="{{ url('/admin/api/automation-credentials') }}" data-method="POST" data-success="reveal" data-reveal-target="#n8n-once" autocomplete="off" novalidate>
                <x-admin.field name="account" id="ac-account" :label="__('admin.mcp.account')" maxlength="120" value="n8n" />
                <fieldset>
                    <legend class="field-label">{{ __('admin.mcp.scopes') }}</legend>
                    <div class="grid grid-cols-2 gap-1">
                        @foreach ($scopes as $scope)
                            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="scopes[]" value="{{ $scope }}" class="size-4 accent-(--color-accent-text)"> <span class="font-mono">{{ $scope }}</span></label>
                        @endforeach
                    </div>
                    <p class="field-error" data-error-for="scopes" hidden></p>
                </fieldset>
                <x-admin.field name="expires_in_days" id="ac-days" type="number" :label="__('admin.mcp.expires_days')" value="365" />
                <x-admin.form-alerts />
                <div><button type="submit" class="admin-btn-primary">{{ __('admin.automation.issue') }}</button></div>
            </form>
            <div class="mt-3">@include('admin.automation._reveal', ['id' => 'n8n-once', 'fields' => ['token' => __('admin.mcp.token'), 'hmac_secret' => __('admin.automation.hmac_secret')]])</div>
        </x-admin.card>
    </div>
    <h2 class="mt-6 mb-3 text-lg font-semibold">{{ __('admin.automation.workflows') }}</h2>
    <p class="mb-3 text-sm text-content-secondary">{{ __('admin.automation.workflows_note') }}</p>
    <x-admin.table :table="$workflows" :title="__('admin.automation.workflows')" :actions="true" />
@endsection
