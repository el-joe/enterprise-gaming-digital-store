@extends('layouts.admin')

@section('title', __('admin.nav.orders'))
@section('heading', __('admin.nav.orders'))

@section('content')
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.orders.intro') }}</p>
    <x-admin.table :table="$table" :search="(string) request('q', '')" :title="__('admin.nav.orders')" />
@endsection
