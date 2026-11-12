{{--
    Product create and edit (M15, Figma "Admin Catalog Edit" 20:1847): basics, delivery,
    availability, descriptions and required delivery fields in one JSON form; then variants,
    status, images, supplier mappings, Arabic text, SEO and delete. Every form posts to the admin
    API with its own permission; without catalog.manage the page is read-only.
--}}
@extends('layouts.admin')

@php
    $canManage = auth()->user()->can('catalog.manage');
    $canPublish = auth()->user()->can('catalog.publish');
    $canMap = auth()->user()->can('mappings.manage');
    $api = $product ? url('/admin/api/products/'.$product->public_id) : url('/admin/api/products');
    $selectedFields = $product ? $product->productDeliveryFields->keyBy('delivery_field_definition_id') : collect();
    $title = $product?->name ?? __('admin.products.new');
@endphp

@section('title', $title)
@section('heading', $product ? __('admin.products.edit') : __('admin.products.new'))

@section('breadcrumbs')
    <x-admin.crumb :href="route('admin.catalog.products.index')">{{ __('admin.tabs.catalog.products') }}</x-admin.crumb>
    <x-admin.crumb>{{ $title }}</x-admin.crumb>
@endsection

@section('content')
    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="flex min-w-0 flex-col gap-4 md:gap-6">
            <form class="flex flex-col gap-4 md:gap-6" data-module="form" data-action="{{ $api }}" data-method="{{ $product ? 'PATCH' : 'POST' }}" data-success="{{ $product ? 'reload' : 'redirect' }}" data-redirect="{{ route('admin.catalog.products.index') }}" novalidate>
                <x-admin.form-alerts />
                @foreach ($groups as $group => $fields)
                    <x-admin.card :title="__('admin.groups.'.$group)">
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($fields as $field)
                                <x-admin.input :field="$field" :record="$product" :disabled="! $canManage" />
                            @endforeach
                        </div>
                    </x-admin.card>
                @endforeach

                <x-admin.card :title="__('admin.products.delivery_fields')" :subtitle="__('admin.products.delivery_fields_note')" :padded="false">
                    <div class="overflow-x-auto" data-rows="delivery_fields">
                        <table class="w-full">
                            <thead><tr>
                                <th class="admin-th">{{ __('admin.products.use_field') }}</th>
                                <th class="admin-th">{{ __('admin.col.label') }}</th>
                                <th class="admin-th">{{ __('admin.col.input_type') }}</th>
                                <th class="admin-th">{{ __('admin.col.validation') }}</th>
                                <th class="admin-th">{{ __('admin.products.required') }}</th>
                                <th class="admin-th">{{ __('admin.col.sort_order') }}</th>
                            </tr></thead>
                            <tbody>
                                @forelse ($definitions as $definition)
                                    @php($chosen = $selectedFields->get($definition->id))
                                    <tr data-row>
                                        <td class="admin-td"><input type="checkbox" class="size-4 accent-(--color-accent-text)" data-row-enabled @checked($chosen) @disabled(! $canManage) aria-label="{{ __('admin.products.use_field_named', ['name' => $definition->label]) }}"><input type="hidden" value="{{ $definition->id }}" data-row-field="delivery_field_definition_id"></td>
                                        <td class="admin-td">{{ $definition->label }} <span class="font-mono text-xs text-content-muted">{{ $definition->code }}</span></td>
                                        <td class="admin-td">{{ \App\DataTables\Cell::label('input_type', $definition->input_type?->value) }}</td>
                                        <td class="admin-td font-mono text-xs" dir="ltr">{{ $definition->validation_regex ?? '—' }}</td>
                                        <td class="admin-td"><input type="checkbox" class="size-4 accent-(--color-accent-text)" data-row-field="is_required" @checked($chosen?->is_required ?? true) @disabled(! $canManage) aria-label="{{ __('admin.products.required') }}"></td>
                                        <td class="admin-td"><input type="number" class="admin-input w-20" value="{{ $chosen?->sort_order ?? 0 }}" data-row-field="sort_order" @disabled(! $canManage) aria-label="{{ __('admin.col.sort_order') }}"></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="admin-td text-center text-content-muted">{{ __('admin.products.no_definitions') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <p class="field-error px-5 pb-3" data-error-for="delivery_fields" hidden></p>
                    </div>
                </x-admin.card>

                @if ($canManage)
                    <div class="flex flex-wrap justify-end gap-3">
                        <a href="{{ route('admin.catalog.products.index') }}" class="admin-btn min-h-10 px-5">{{ __('admin.common.cancel') }}</a>
                        <button type="submit" class="admin-btn-primary min-h-10 px-5" data-resource-submit><x-store.icon name="check" :size="16" /> {{ __('admin.products.save') }}</button>
                    </div>
                @else
                    <p class="text-sm text-content-muted">{{ __('admin.common.read_only') }}</p>
                @endif
            </form>

            @if ($product)
                <x-admin.card :title="__('admin.products.variants')" :subtitle="__('admin.products.variants_note')" :padded="false">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead><tr>
                                <th class="admin-th">{{ __('admin.fields.title') }}</th>
                                <th class="admin-th">{{ __('admin.fields.region') }}</th>
                                <th class="admin-th">{{ __('admin.fields.sku') }}</th>
                                <th class="admin-th text-end">{{ __('admin.fields.denomination_value') }}</th>
                                <th class="admin-th">{{ __('admin.fields.availability') }}</th>
                                <th class="admin-th">{{ __('admin.col.active') }}</th>
                            </tr></thead>
                            <tbody>
                                @forelse ($product->productVariants as $variant)
                                    <tr>
                                        <td class="admin-td font-medium">{{ $variant->title }}</td>
                                        <td class="admin-td">{{ $variant->region?->name ?? '—' }}</td>
                                        <td class="admin-td font-mono text-xs" dir="ltr">{{ $variant->sku }}</td>
                                        <td class="admin-td text-end" dir="ltr">{{ $variant->denomination_value === null ? '—' : $variant->denomination_value.' '.$variant->denomination_currency }}</td>
                                        <td class="admin-td"><x-admin.status group="variant_availability" :value="$variant->availability?->value" /></td>
                                        <td class="admin-td"><x-admin.badge :tone="$variant->is_active ? 'success' : 'neutral'" :dot="false">{{ $variant->is_active ? __('admin.common.yes') : __('admin.common.no') }}</x-admin.badge></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="admin-td pt-0">
                                            <details>
                                                <summary class="cursor-pointer text-sm font-medium text-accent-text">{{ $canManage ? __('admin.products.edit_variant') : __('admin.products.view_variant') }}</summary>
                                                <form class="mt-3 grid gap-3 md:grid-cols-2" data-module="form" data-action="{{ $api.'/variants/'.$variant->public_id }}" data-method="PATCH" data-success="reload" novalidate>
                                                    @foreach ($variantFields as $field)
                                                        <x-admin.input :field="$field" :record="$variant" :disabled="! $canManage" :id-prefix="'v'.$variant->id" />
                                                    @endforeach
                                                    <div class="md:col-span-2"><x-admin.form-alerts /></div>
                                                    @if ($canManage)
                                                        <div class="flex flex-wrap gap-2 md:col-span-2" data-module="action">
                                                            <button type="submit" class="admin-btn-primary">{{ __('admin.common.save') }}</button>
                                                            <button type="button" class="admin-btn text-status-danger" data-action-url="{{ $api.'/variants/'.$variant->public_id }}" data-action-method="DELETE" data-confirm-title="{{ __('admin.common.delete_confirm', ['name' => $variant->title]) }}" data-confirm-danger>{{ __('admin.common.delete') }}</button>
                                                        </div>
                                                    @endif
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="admin-td text-center text-content-muted">{{ __('admin.products.no_variants') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($canManage)
                        <details class="border-t border-line-subtle px-5 py-4">
                            <summary class="admin-btn inline-flex cursor-pointer"><x-store.icon name="plus" :size="16" /> {{ __('admin.products.add_variant') }}</summary>
                            <form class="mt-3 grid gap-3 md:grid-cols-2" data-module="form" data-action="{{ $api.'/variants' }}" data-method="POST" data-success="reload" novalidate>
                                @foreach ($variantFields as $field)
                                    <x-admin.input :field="$field" id-prefix="vnew" />
                                @endforeach
                                <div class="md:col-span-2"><x-admin.form-alerts /></div>
                                <div class="md:col-span-2"><button type="submit" class="admin-btn-primary">{{ __('admin.products.add_variant') }}</button></div>
                            </form>
                        </details>
                    @endif
                </x-admin.card>
            @endif
        </div>

        <div class="flex flex-col gap-4 md:gap-6">
            @if ($product)
                <x-admin.card :title="__('admin.products.status')">
                    <div class="flex items-center justify-between gap-3">
                        <x-admin.status group="product_status" :value="$product->status?->value" />
                        @if ($canPublish)
                            <div data-module="action">
                                @if ($product->status?->value === 'active')
                                    <button type="button" class="admin-btn text-status-danger" data-action-url="{{ $api.'/deactivate' }}" data-confirm-title="{{ __('admin.products.unpublish_confirm') }}" data-confirm-danger>{{ __('admin.products.unpublish') }}</button>
                                @else
                                    <button type="button" class="admin-btn-primary" data-action-url="{{ $api.'/activate' }}" data-confirm-title="{{ __('admin.products.publish_confirm') }}">{{ __('admin.products.publish') }}</button>
                                @endif
                            </div>
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-content-muted">{{ __('admin.products.publish_note') }}</p>
                </x-admin.card>

                <x-admin.card :title="__('admin.products.images')" :subtitle="__('admin.products.images_note')">
                    <ul class="grid grid-cols-2 gap-3" data-module="action">
                        @forelse ($product->productImages as $image)
                            <li class="flex flex-col gap-2">
                                @if ($image->media)
                                    <img src="{{ $mediaUrls->url((string) $image->media->disk, (string) $image->media->path) }}" alt="{{ $image->alt ?? '' }}" class="aspect-video w-full rounded-md bg-surface-sunken object-cover" loading="lazy">
                                @endif
                                <p class="flex flex-wrap gap-1 text-xs">
                                    @if ($loop->first)<x-admin.badge tone="success">{{ __('admin.products.primary') }}</x-admin.badge>@endif
                                    @if ($image->media?->source?->value === 'supplier')<x-admin.badge>{{ __('admin.products.supplier_image') }}</x-admin.badge>@endif
                                </p>
                                @if ($canManage && ! $loop->first)
                                    <button type="button" class="admin-btn h-8" data-action-url="{{ $api.'/images/'.$image->id.'/primary' }}">{{ __('admin.products.make_primary') }}</button>
                                @endif
                                @if ($canManage)
                                    <button type="button" class="admin-btn h-8 text-status-danger" data-action-url="{{ $api.'/images/'.$image->id }}" data-action-method="DELETE" data-confirm-title="{{ __('admin.products.remove_image') }}" data-confirm-danger>{{ __('admin.products.remove_image') }}</button>
                                @endif
                            </li>
                        @empty
                            <li class="col-span-2 text-sm text-content-muted">{{ __('admin.products.no_images') }}</li>
                        @endforelse
                    </ul>
                    @if ($canManage)
                        <form class="mt-4 flex flex-col gap-3 border-t border-line-subtle pt-4" data-module="form" data-action="{{ $api.'/images' }}" data-method="POST" data-success="reload" novalidate>
                            <x-admin.image-upload name="media_id" id="pi-media" :label="__('admin.products.new_image')" :help="__('admin.help.image_upload')" required />
                            <x-admin.field name="alt" id="pi-alt" :label="__('admin.media.alt')" maxlength="190" />
                            <x-admin.form-alerts />
                            <div><button type="submit" class="admin-btn">{{ __('admin.products.attach_image') }}</button></div>
                        </form>
                    @endif
                </x-admin.card>

                <x-admin.card :title="__('admin.products.suppliers')" :subtitle="__('admin.products.suppliers_note')">
                    <ul class="flex flex-col gap-2 text-sm" data-module="action">
                        @forelse ($mappings as $mapping)
                            <li class="flex items-start justify-between gap-3 rounded-md border border-line-subtle p-2">
                                <span class="min-w-0">
                                    <span class="block font-medium">{{ $mapping->supplier?->name }} · <x-admin.status group="mapping_role" :value="$mapping->role?->value" :dot="false" /></span>
                                    <span class="block text-xs text-content-muted" dir="ltr">{{ $product->productVariants->firstWhere('id', $mapping->product_variant_id)?->sku }} → {{ $mapping->supplierProduct?->external_id }} · {{ $mapping->supplier_unit_price }} {{ $mapping->supplier_unit_currency }}</span>
                                </span>
                                @if ($canMap)
                                    <button type="button" class="admin-btn h-8 shrink-0 text-status-danger" data-action-url="{{ url('/admin/api/supplier-mappings/'.$mapping->id) }}" data-action-method="DELETE" data-confirm-title="{{ __('admin.products.remove_mapping') }}" data-confirm-danger>{{ __('admin.common.delete') }}</button>
                                @endif
                            </li>
                        @empty
                            <li class="text-content-muted">{{ __('admin.products.no_mappings') }}</li>
                        @endforelse
                    </ul>
                    @if ($canMap && $product->productVariants->isNotEmpty())
                        <details class="mt-4 border-t border-line-subtle pt-4">
                            <summary class="cursor-pointer text-sm font-medium text-accent-text">{{ __('admin.products.add_mapping') }}</summary>
                            <form class="mt-3 flex flex-col gap-3" data-module="form" data-action="{{ url('/admin/api/supplier-mappings') }}" data-method="POST" data-success="reload" novalidate>
                                <div>
                                    <label for="sm-variant" class="field-label">{{ __('admin.col.variant') }}</label>
                                    <select id="sm-variant" name="product_variant_id" class="admin-input">@foreach ($product->productVariants as $variant)<option value="{{ $variant->id }}">{{ $variant->title }} · {{ $variant->sku }}</option>@endforeach</select>
                                    <p class="field-error" data-error-for="product_variant_id" hidden></p>
                                </div>
                                <div>
                                    <label for="sm-supplier" class="field-label">{{ __('admin.col.supplier') }}</label>
                                    <select id="sm-supplier" name="supplier_id" class="admin-input">@foreach ($suppliers as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
                                    <p class="field-error" data-error-for="supplier_id" hidden></p>
                                </div>
                                <x-admin.field name="supplier_product_id" id="sm-product" type="number" :label="__('admin.products.supplier_product_id')" dir="ltr" data-nullable />
                                <div>
                                    <label for="sm-role" class="field-label">{{ __('admin.mappings.col.role') }}</label>
                                    <select id="sm-role" name="role" class="admin-input">@foreach (['primary', 'secondary', 'other'] as $role)<option value="{{ $role }}">{{ \App\DataTables\Cell::label('mapping_role', $role) }}</option>@endforeach</select>
                                    <p class="field-error" data-error-for="role" hidden></p>
                                </div>
                                <x-admin.field name="priority" id="sm-priority" type="number" :label="__('admin.mappings.col.priority')" value="100" />
                                <x-admin.field name="supplier_unit_price" id="sm-price" :label="__('admin.mappings.col.price')" inputmode="decimal" dir="ltr" />
                                <x-admin.field name="supplier_unit_currency" id="sm-currency" :label="__('admin.fields.currency')" value="USD" maxlength="3" dir="ltr" />
                                <x-admin.form-alerts />
                                <div><button type="submit" class="admin-btn-primary">{{ __('admin.products.add_mapping') }}</button></div>
                            </form>
                        </details>
                    @endif
                </x-admin.card>

                @include('admin.partials.translations', ['type' => 'product', 'fields' => (array) config('admin.translations.product.fields'), 'record' => $product, 'canManage' => $canManage])
                @include('admin.partials.seo', ['type' => 'product', 'record' => $product])

                @if ($canManage)
                    <x-admin.card :title="__('admin.common.danger_zone')">
                        <p class="text-sm text-content-secondary">{{ __('admin.common.delete_text') }}</p>
                        <div class="mt-3" data-module="action">
                            <button type="button" class="admin-btn-danger" data-action-url="{{ $api }}" data-action-method="DELETE" data-confirm-title="{{ __('admin.common.delete_confirm', ['name' => $product->name]) }}" data-confirm-danger data-redirect="{{ route('admin.catalog.products.index') }}"><x-store.icon name="trash" :size="16" /> {{ __('admin.common.delete') }}</button>
                        </div>
                    </x-admin.card>
                @endif
            @endif
        </div>
    </div>
@endsection
