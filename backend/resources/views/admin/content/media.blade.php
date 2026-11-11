{{-- Media library (M15, spec Phase 9.9): upload (validated and re-encoded on the server), ids for forms, alt text, delete when unused. --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.content.media'))
@section('heading', __('admin.nav.content'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'media'])
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.media.intro') }}</p>
    <div class="grid gap-4 md:gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <x-admin.table :table="$table" :title="__('admin.tabs.content.media')" :actions="true" />
        <x-admin.card :title="__('admin.media.upload')" :subtitle="__('admin.media.upload_note', ['size' => intdiv($maxKilobytes, 1024)])">
            <form class="flex flex-col gap-3" data-module="form" data-multipart data-action="{{ url('/admin/api/media') }}" data-method="POST" data-success="reveal" data-reveal-target="#media-uploaded" novalidate>
                <div>
                    <label for="mu-file" class="field-label">{{ __('admin.media.file') }}</label>
                    <input id="mu-file" type="file" name="file" accept="image/jpeg,image/png,image/webp,image/avif" class="admin-input py-1.5">
                    <p class="field-error" data-error-for="file" hidden></p>
                </div>
                <x-admin.field name="alt" id="mu-alt" :label="__('admin.media.alt')" maxlength="190" />
                <x-admin.field name="folder" id="mu-folder" :label="__('admin.media.folder')" maxlength="190" />
                <x-admin.field name="license_note" id="mu-license" :label="__('admin.media.license')" maxlength="255" :help="__('admin.media.license_help')" />
                <x-admin.form-alerts />
                <div><button type="submit" class="admin-btn-primary"><x-store.icon name="plus" :size="16" /> {{ __('admin.media.upload') }}</button></div>
            </form>
            <div id="media-uploaded" class="alert-success mt-3" hidden role="status">
                <x-store.icon name="check" class="mt-0.5 text-status-success" />
                <p>{{ __('admin.media.uploaded') }} <span class="font-mono" dir="ltr" data-reveal-field="id"></span></p>
            </div>
        </x-admin.card>
    </div>
@endsection
