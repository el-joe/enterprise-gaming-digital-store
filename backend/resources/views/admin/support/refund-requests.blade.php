{{-- Refund requests (M15, spec Phase 9.10). --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.support.refund-requests'))
@section('heading', __('admin.nav.support'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'refund-requests'])
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.refund_requests.intro') }}</p>
    <x-admin.table :table="$table" :title="__('admin.tabs.support.refund-requests')" />
@endsection
