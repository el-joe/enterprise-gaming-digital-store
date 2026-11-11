@extends('layouts.account')

@section('title', __('account.preferences.title'))
@section('nav', 'notifications')

@section('page')
    <div>
        <a href="{{ url()->localized('storefront.account.notifications') }}" class="inline-flex items-center gap-1 text-sm text-content-secondary hover:text-content-primary"><x-store.icon name="chevron-back" :size="16" /> {{ __('account.notifications.title') }}</a>
        <x-account.page-head class="mt-2" :title="__('account.preferences.title')" :subtitle="__('account.preferences.subtitle')" />
    </div>

    @php($channels = ['mail', 'database', 'sms'])
    @php($index = 0)
    <form class="flex flex-col gap-4" data-module="account-form" data-action="{{ url('/api/v1/account/notification-preferences') }}" data-method="PATCH" novalidate>
        <div class="overflow-x-auto rounded-xl border border-line-subtle bg-surface-raised">
            <table class="w-full min-w-[480px] text-sm">
                <thead class="bg-surface-overlay/60 text-xs text-content-secondary">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-start font-medium">{{ __('account.preferences.category') }}</th>
                        @foreach ($channels as $channel)
                            <th scope="col" class="px-4 py-3 text-center font-medium">{{ __('account.preferences.channels.'.$channel) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-subtle">
                    @foreach ($preferences as $category => $cells)
                        <tr>
                            <th scope="row" class="px-4 py-3 text-start font-medium">
                                {{ __('account.preferences.categories.'.$category) }}
                                <span class="block text-xs font-normal text-content-muted">{{ __('account.preferences.hints.'.$category) }}</span>
                            </th>
                            @foreach ($channels as $channel)
                                @php($cell = collect($cells)->firstWhere('channel', $channel))
                                <td class="px-4 py-3 text-center">
                                    @if ($cell)
                                        @if ($cell['mandatory'])
                                            <input type="checkbox" checked disabled class="size-5 accent-(--color-accent-primary)" aria-label="{{ __('account.preferences.cell', ['category' => __('account.preferences.categories.'.$category), 'channel' => __('account.preferences.channels.'.$channel)]) }} ({{ __('account.preferences.mandatory') }})">
                                        @else
                                            <input type="hidden" name="preferences[{{ $index }}][category]" value="{{ $category }}">
                                            <input type="hidden" name="preferences[{{ $index }}][channel]" value="{{ $channel }}">
                                            <input type="checkbox" name="preferences[{{ $index }}][enabled]" @checked($cell['enabled']) class="size-5 accent-(--color-accent-primary)" aria-label="{{ __('account.preferences.cell', ['category' => __('account.preferences.categories.'.$category), 'channel' => __('account.preferences.channels.'.$channel)]) }}">
                                            @php($index++)
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-content-muted">{{ __('account.preferences.mandatory_note') }}</p>
        <div class="alert-danger" data-form-error hidden role="alert"><p data-form-error-text></p></div>
        <div class="alert-success" data-form-success hidden role="status"><x-store.icon name="check" class="mt-0.5 text-status-success" /><p data-form-success-text></p></div>
        <div><button type="submit" class="btn-primary">{{ __('account.common.save') }}</button></div>
    </form>
@endsection
