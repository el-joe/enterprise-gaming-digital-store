{{-- Support tickets queue (M15, Figma "Admin / Support / Tickets"). --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.support.tickets'))
@section('heading', __('admin.nav.support'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'tickets'])
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.tickets.intro') }}</p>
    <x-admin.table :table="$table" :title="__('admin.tabs.support.tickets')" />
@endsection
