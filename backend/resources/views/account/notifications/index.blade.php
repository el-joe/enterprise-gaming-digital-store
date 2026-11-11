@extends('layouts.account')

@section('title', __('account.notifications.title'))
@section('nav', 'notifications')

@section('page')
    <x-account.page-head :title="__('account.notifications.title')" :subtitle="trans_choice('account.notifications.unread', $unread, ['count' => $unread])">
        <div class="flex flex-wrap gap-2" data-module="account-action">
            @if ($unread > 0)
                <button type="button" class="btn-secondary min-h-9 px-3.5 text-xs" data-action-url="{{ url('/api/v1/account/notifications/read-all') }}" data-action-reload>{{ __('account.notifications.read_all') }}</button>
            @endif
            <a href="{{ url()->localized('storefront.account.notifications.preferences') }}" class="btn-ghost min-h-9 px-3.5 text-xs"><x-store.icon name="settings" :size="16" /> {{ __('account.notifications.preferences') }}</a>
        </div>
    </x-account.page-head>

    @if ($notifications === [])
        <x-store.empty-state art="no-notifications" :title="__('account.notifications.empty_title')" :text="__('account.notifications.empty_text')" class="rounded-xl border border-line-subtle bg-surface-raised" />
    @else
        <ul class="flex flex-col gap-2" data-module="account-action">
            @foreach ($notifications as $notification)
                @php($target = match ($notification['link']['type'] ?? null) {
                    'order' => url()->localized('storefront.account.orders.show', ['orderPublicId' => $notification['link']['value']]),
                    'ticket' => url()->localized('storefront.account.tickets.show', ['ticketPublicId' => $notification['link']['value']]),
                    default => null,
                })
                <li @class(['flex items-start gap-3 rounded-xl border p-4', 'border-accent-primary/40 bg-surface-raised' => ! $notification['read_at'], 'border-line-subtle bg-surface-base' => $notification['read_at']])>
                    <span @class(['mt-1.5 size-2 shrink-0 rounded-full', 'bg-accent-primary' => ! $notification['read_at'], 'bg-transparent' => $notification['read_at']]) aria-hidden="true"></span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold">
                            @if (! $notification['read_at'])<span class="sr-only">{{ __('account.notifications.new') }}: </span>@endif
                            {{ $notification['title'] ?? __('account.notifications.generic') }}
                        </p>
                        @if ($notification['body'])<p class="mt-0.5 text-sm text-content-secondary">{{ $notification['body'] }}</p>@endif
                        <p class="mt-1 text-xs text-content-muted"><x-account.time :at="$notification['created_at']" format="relative" /></p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-2">
                        @if ($target)
                            <a href="{{ $target }}" class="text-sm font-medium text-accent-text">{{ __('account.notifications.open') }}</a>
                        @endif
                        @unless ($notification['read_at'])
                            <button type="button" class="text-xs text-content-muted hover:text-content-primary" data-action-url="{{ url('/api/v1/account/notifications/'.$notification['id'].'/read') }}" data-action-reload>{{ __('account.notifications.mark_read') }}</button>
                        @endunless
                    </div>
                </li>
            @endforeach
        </ul>
        @include('account.partials.pagination', ['paginator' => $paginator])
    @endif
@endsection
