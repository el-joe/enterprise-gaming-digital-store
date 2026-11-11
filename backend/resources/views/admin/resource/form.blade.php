{{--
    Create or edit page of a declarative admin screen (M15, Figma "Admin Catalog Edit" pattern):
    one JSON form (cards per field group) posting to the admin API, errors beside the fields,
    a delete action, and for existing records the Arabic translation and SEO cards. Without the
    manage permission every control is disabled and nothing can be sent.
--}}
@extends('layouts.admin')

@php
    $slug = $screen::slug();
    $title = $screen->title($record);
@endphp

@section('title', $title)
@section('heading', __('admin.nav.'.$screen::section()))

@section('breadcrumbs')
    <x-admin.crumb :href="$screen->indexUrl()">{{ __('admin.tabs.'.$screen::section().'.'.$slug) }}</x-admin.crumb>
    <x-admin.crumb>{{ $title }}</x-admin.crumb>
@endsection

@section('content')
    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <form class="flex min-w-0 flex-col gap-4 md:gap-6" data-module="form"
            data-action="{{ $screen->apiUrl($record) }}"
            data-method="{{ $record === null ? 'POST' : $screen->updateMethod() }}"
            data-success="{{ $record === null ? 'redirect' : 'reload' }}"
            data-redirect="{{ $screen->indexUrl() }}"
            novalidate>
            <x-admin.form-alerts />
            @foreach ($groups as $group => $fields)
                <x-admin.card :title="__('admin.groups.'.$group)">
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($fields as $field)
                            <x-admin.input :field="$field" :record="$record" :disabled="! $canManage" />
                        @endforeach
                    </div>
                </x-admin.card>
            @endforeach
            @if ($canManage)
                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ $screen->indexUrl() }}" class="admin-btn min-h-10 px-5">{{ __('admin.common.cancel') }}</a>
                    <button type="submit" class="admin-btn-primary min-h-10 px-5" data-resource-submit><x-store.icon name="check" :size="16" /> {{ $record === null ? __('admin.common.create') : __('admin.common.save') }}</button>
                </div>
            @else
                <p class="text-sm text-content-muted">{{ __('admin.common.read_only') }}</p>
            @endif
        </form>

        <div class="flex flex-col gap-4 md:gap-6">
            @if ($record !== null)
                @if ($screen->panel())
                    @include($screen->panel(), ['record' => $record, 'screen' => $screen, 'canManage' => $canManage, 'panel' => $panel])
                @endif
                @if ($screen->translations())
                    @include('admin.partials.translations', ['type' => $screen->translations()['type'], 'fields' => $screen->translations()['fields'], 'record' => $record, 'canManage' => $canManage])
                @endif
                @if ($screen->seoType())
                    @include('admin.partials.seo', ['type' => $screen->seoType(), 'record' => $record])
                @endif
                @if ($screen::deletable() && $canManage)
                    <x-admin.card :title="__('admin.common.danger_zone')">
                        <p class="text-sm text-content-secondary">{{ __('admin.common.delete_text') }}</p>
                        <div class="mt-3" data-module="action">
                            <button type="button" class="admin-btn-danger" data-action-url="{{ $screen->apiUrl($record) }}" data-action-method="DELETE" data-confirm-title="{{ __('admin.common.delete_confirm', ['name' => $title]) }}" data-confirm-danger data-redirect="{{ $screen->indexUrl() }}"><x-store.icon name="trash" :size="16" /> {{ __('admin.common.delete') }}</button>
                        </div>
                    </x-admin.card>
                @endif
            @endif
        </div>
    </div>
@endsection
