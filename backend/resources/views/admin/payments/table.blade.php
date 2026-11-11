@extends('layouts.admin')

@section('title', __('admin.payments.tab.'.$tab))
@section('heading', __('admin.nav.payments'))

@section('content')
    @include('admin.payments._tabs', ['current' => $tab])
    <p class="mb-4 text-sm text-content-secondary">{{ __('admin.payments.intro.'.$tab) }}</p>
    <x-admin.table :table="$table" :title="__('admin.payments.tab.'.$tab)" />
@endsection
