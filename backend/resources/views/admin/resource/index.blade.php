{{-- List page of a declarative admin screen (M15): section tabs, intro, "new" and the server-side table. --}}
@extends('layouts.admin')

@section('title', __('admin.tabs.'.$screen::section().'.'.$screen::slug()))
@section('heading', __('admin.nav.'.$screen::section()))

@section('content')
    @include('admin.partials.tabs', ['current' => $screen::slug()])
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-content-secondary">{{ __('admin.screens.'.$screen::slug().'.intro') }}</p>
        @if ($screen::creatable() && $screen->canManage())
            <a href="{{ route($screen::routeName('create')) }}" class="admin-btn-primary"><x-store.icon name="plus" :size="16" /> {{ __('admin.screens.'.$screen::slug().'.new') }}</a>
        @endif
    </div>
    <x-admin.table :table="$table" :title="__('admin.tabs.'.$screen::section().'.'.$screen::slug())" />
@endsection
