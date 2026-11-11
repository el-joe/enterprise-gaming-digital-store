@extends('layouts.admin-auth')

@section('title', __('admin.auth.login_title'))

@section('content')
    <h1 class="font-display text-2xl leading-8 font-bold">{{ __('admin.auth.login_title') }}</h1>
    <p class="mt-1 text-sm text-content-secondary">{{ __('admin.auth.login_intro') }}</p>
    <form class="mt-6 flex flex-col gap-4" data-module="auth-form" data-action="{{ url('/login') }}" data-kind="login"
        data-redirect="{{ route('admin.home') }}" data-two-factor-url="{{ route('admin.two-factor') }}" novalidate>
        <x-admin.field name="email" type="email" :label="__('admin.auth.email')" autocomplete="username" required dir="ltr" />
        <x-admin.field name="password" type="password" :label="__('admin.auth.password')" autocomplete="current-password" required />
        <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5 text-status-danger" /><p data-form-error-text></p></div>
        <button type="submit" class="admin-btn-primary min-h-11 w-full">{{ __('admin.auth.sign_in') }}</button>
    </form>
@endsection
