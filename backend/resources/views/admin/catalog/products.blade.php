{{-- Products list (M15, Figma "Admin Catalog List"): table with status, platform and bulk publish. --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.catalog.products'))
@section('heading', __('admin.nav.catalog'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'products'])
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-content-secondary">{{ __('admin.products.intro') }}</p>
        @can('catalog.manage')
            <a href="{{ route('admin.catalog.products.create') }}" class="admin-btn-primary"><x-store.icon name="plus" :size="16" /> {{ __('admin.products.new') }}</a>
        @endcan
    </div>
    <x-admin.table :table="$table" :search="$search" :title="__('admin.tabs.catalog.products')" />
@endsection
