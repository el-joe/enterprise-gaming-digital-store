{{-- Customers list (M15, Figma "Admin / Customers / List"). --}}
@extends('layouts.admin')

@section('title', __('admin.nav.customers'))
@section('heading', __('admin.nav.customers'))

@section('content')
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.customers.intro') }}</p>
    <x-admin.table :table="$table" :title="__('admin.nav.customers')" />
@endsection
