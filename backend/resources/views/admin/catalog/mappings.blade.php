{{-- Supplier mappings (M15, spec Phase 5 and 9.3): every mapping, bulk on/off, and bulk mapping by lines. --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.catalog.mappings'))
@section('heading', __('admin.nav.catalog'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'mappings'])
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.mappings_bulk.intro') }}</p>
    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <x-admin.table :table="$table" :title="__('admin.tabs.catalog.mappings')" />
        <x-admin.card :title="__('admin.mappings_bulk.title')" :subtitle="__('admin.mappings_bulk.note')">
            <form class="flex flex-col gap-3" data-module="form" data-action="{{ route('admin.api.supplier-mappings.bulk') }}" data-method="POST" data-success="reload" novalidate>
                <div>
                    <label for="bm-supplier" class="field-label">{{ __('admin.col.supplier') }}</label>
                    <select id="bm-supplier" name="supplier_id" class="admin-input">
                        @foreach ($suppliers as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                    </select>
                    <p class="field-error" data-error-for="supplier_id" hidden></p>
                </div>
                <div>
                    <label for="bm-rows" class="field-label">{{ __('admin.mappings_bulk.rows') }}</label>
                    <textarea id="bm-rows" name="rows" rows="10" class="admin-input py-2 font-mono text-xs" dir="ltr" placeholder="STM-US-20,15187,19.40,USD,primary,10"></textarea>
                    <p class="field-help">{{ __('admin.mappings_bulk.format') }}</p>
                    <p class="field-error" data-error-for="rows" hidden></p>
                </div>
                <x-admin.form-alerts />
                <div><button type="submit" class="admin-btn-primary">{{ __('admin.mappings_bulk.submit') }}</button></div>
            </form>
        </x-admin.card>
    </div>
@endsection
