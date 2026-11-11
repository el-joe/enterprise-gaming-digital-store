{{--
    Account layout (M13, Figma "Account" pages): the storefront chrome, the account navigation
    (sidebar from lg, horizontal tabs below) and the email verification banner. Pages set
    `title`, `nav` (the current navigation key) and `page`. Never indexed.
--}}
@extends('layouts.storefront')

@section('robots', 'noindex,nofollow')

@section('content')
    <div class="container-page py-6 md:py-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-8">
            <x-account.nav :current="trim($__env->yieldContent('nav'))" :unread="$account['unread'] ?? 0" />

            <div class="flex min-w-0 flex-1 flex-col gap-6">
                @if (isset($account) && ! $account['email_verified'])
                    <div class="alert-warning" data-module="account-action" data-verify-banner>
                        <x-store.icon name="mail" class="mt-0.5 text-status-warning" />
                        <div class="flex flex-1 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p>{{ __('account.verify.text') }}</p>
                            <button type="button" class="btn-secondary min-h-9 shrink-0 px-3 text-xs"
                                data-action-url="{{ url('/email/verification-notification') }}" data-action-method="POST"
                                data-action-done="{{ __('account.verify.sent') }}">{{ __('account.verify.resend') }}</button>
                        </div>
                    </div>
                @endif

                @yield('page')
            </div>
        </div>
    </div>
@endsection
