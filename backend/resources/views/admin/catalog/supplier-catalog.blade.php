{{-- Supplier catalog: products synced from the suppliers, with "Add to store" (draft products, variants, primary mappings). --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.catalog.supplier-catalog'))
@section('heading', __('admin.nav.catalog'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'supplier-catalog'])
    <div class="mb-4 flex flex-col gap-1 text-sm text-content-secondary">
        <p>{{ __('admin.supplier_catalog.intro') }}</p>
        <p>{{ __('admin.supplier_catalog.intro_add') }}</p>
    </div>
    @if ($canCreateCategories)
        <form class="mb-4 flex flex-wrap items-center gap-3" data-module="form" data-action="{{ route('admin.api.supplier-catalog.create-categories') }}" data-method="POST" data-success="reload" novalidate>
            <button type="submit" class="admin-btn">{{ __('admin.supplier_catalog.create_categories') }}</button>
            <span class="text-xs text-content-muted">{{ __('admin.supplier_catalog.create_categories_help') }}</span>
            <x-admin.form-alerts />
        </form>
    @endif
    <x-admin.table :table="$table" :title="__('admin.tabs.catalog.supplier-catalog')" :actions="$canAdd" />

    @if ($canAdd)
        <template id="{{ \App\DataTables\Catalog\SupplierCatalogDataTable::FIELDS_TEMPLATE }}">
            <div class="flex flex-col gap-3 text-start">
                <div>
                    <label for="sc-category" class="field-label">{{ __('admin.supplier_catalog.category') }}</label>
                    <select id="sc-category" name="primary_category_id" class="admin-input" aria-describedby="sc-category-error sc-category-help">
                        <option value="" selected>{{ __('admin.supplier_catalog.category_supplier') }}</option>
                        @foreach ($categories as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                    </select>
                    <p id="sc-category-help" class="field-help">{{ __('admin.supplier_catalog.category_help') }}</p>
                    <p id="sc-category-error" class="field-error" data-error-for="primary_category_id" hidden></p>
                </div>
                <div>
                    <label for="sc-platform" class="field-label">{{ __('admin.supplier_catalog.platform') }}</label>
                    <select id="sc-platform" name="gaming_platform_id" class="admin-input" aria-describedby="sc-platform-error">
                        <option value="" selected>{{ __('admin.supplier_catalog.platform_auto') }}</option>
                        @foreach ($platforms as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                    </select>
                    <p class="field-help">{{ __('admin.supplier_catalog.platform_help') }}</p>
                    <p id="sc-platform-error" class="field-error" data-error-for="gaming_platform_id" hidden></p>
                </div>
                <p class="field-help">{{ __('admin.supplier_catalog.add_help') }}</p>
            </div>
        </template>
    @endif
@endsection
