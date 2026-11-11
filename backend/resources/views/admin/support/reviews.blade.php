{{-- Review moderation queue (M15, Figma "Admin / Reviews / Moderation Queue"): approve, reject with a reason, feature. --}}
@extends('layouts.admin')

@section('title', __('admin.nav.reviews'))
@section('heading', __('admin.nav.reviews'))

@section('content')
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.reviews.intro') }}</p>
    <x-admin.table :table="$table" :title="__('admin.nav.reviews')" :actions="true" />
@endsection
