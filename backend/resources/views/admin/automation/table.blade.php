{{-- An AI and automation queue (M15): AI draft review or MCP action approvals, decided from the row. --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.automation.'.$tab))
@section('heading', __('admin.nav.automation'))

@section('content')
    @include('admin.partials.tabs', ['current' => $tab])
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.automation.intro.'.$tab) }}</p>
    <x-admin.table :table="$table" :title="__('admin.tabs.automation.'.$tab)" :actions="true" />
@endsection
