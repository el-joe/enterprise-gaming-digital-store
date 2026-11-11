@extends('layouts.account')

@section('title', $ticket ? $ticket['subject'] : __('account.tickets.title'))
@section('nav', 'tickets')

@section('page')
    <x-account.page-head :title="__('account.tickets.title')" />

    <div class="grid gap-4 lg:grid-cols-[340px_minmax(0,1fr)] lg:items-start">
        <div @class(['flex flex-col gap-3', 'hidden lg:flex' => $ticket !== null])>
            <a href="{{ url()->localized('storefront.account.tickets.create') }}" class="btn-primary w-full"><x-store.icon name="plus" :size="18" /> {{ __('account.tickets.new') }}</a>
            @if ($tickets === [])
                <x-store.empty-state art="no-tickets" :title="__('account.tickets.empty_title')" :text="__('account.tickets.empty_text')" class="rounded-xl border border-line-subtle bg-surface-raised" />
            @else
                <ul class="flex flex-col gap-2">
                    @foreach ($tickets as $row)
                        @php($active = $ticket !== null && $ticket['public_id'] === $row['public_id'])
                        <li>
                            <a href="{{ url()->localized('storefront.account.tickets.show', ['ticketPublicId' => $row['public_id']]) }}" @if ($active) aria-current="page" @endif @class([
                                'block rounded-xl border bg-surface-raised p-4 transition hover:border-line-strong',
                                'border-accent-primary' => $active,
                                'border-line-subtle' => ! $active,
                            ])>
                                <span class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-xs text-content-muted" dir="ltr">{{ $row['ticket_number'] }}</span>
                                    <x-account.ticket-status :status="$row['status']" />
                                </span>
                                <span class="mt-1 block truncate font-semibold">{{ $row['subject'] }}</span>
                                <span class="mt-1 block text-xs text-content-muted">
                                    @if ($row['order']){{ __('account.codes.order') }} <span dir="ltr">{{ $row['order']['order_number'] }}</span>@else{{ __('account.tickets.categories.'.$row['category']) }}@endif
                                    · <x-account.time :at="$row['last_activity_at'] ?? $row['created_at']" />
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @include('account.partials.pagination', ['paginator' => $paginator])
            @endif
        </div>

        @if ($ticket)
            <section class="flex flex-col gap-4 rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-5" aria-labelledby="ticket-subject">
                <a href="{{ url()->localized('storefront.account.tickets') }}" class="inline-flex items-center gap-1 text-sm text-content-secondary hover:text-content-primary lg:hidden"><x-store.icon name="chevron-back" :size="16" /> {{ __('account.tickets.all') }}</a>
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h2 id="ticket-subject" class="text-lg font-semibold">{{ $ticket['subject'] }}</h2>
                        <p class="text-xs text-content-muted">
                            <span dir="ltr">{{ $ticket['ticket_number'] }}</span> · {{ __('account.tickets.categories.'.$ticket['category']) }}
                            @if ($ticket['order']) · <a href="{{ url()->localized('storefront.account.orders.show', ['orderPublicId' => $ticket['order']['public_id']]) }}" class="text-accent-text">{{ __('account.codes.order') }} <span dir="ltr">{{ $ticket['order']['order_number'] }}</span></a>@endif
                        </p>
                    </div>
                    <x-account.ticket-status :status="$ticket['status']" />
                </div>

                <ol class="flex flex-col gap-3" aria-label="{{ __('account.tickets.messages') }}">
                    @foreach ($ticket['messages'] as $message)
                        @php($mine = $message['from'] === 'customer')
                        <li @class(['rounded-md p-3', 'bg-surface-sunken' => $mine, 'bg-surface-overlay' => ! $mine])>
                            <p class="text-xs text-content-muted">{{ $mine ? __('account.tickets.you') : __('account.tickets.support') }} · <x-account.time :at="$message['created_at']" format="datetime" /></p>
                            <p class="mt-1 text-sm whitespace-pre-line">{{ $message['body'] }}</p>
                        </li>
                    @endforeach
                </ol>

                @if ($ticket['status'] === 'closed')
                    <p class="text-sm text-content-secondary">{{ __('account.tickets.closed_note') }}</p>
                @else
                    <form class="flex flex-col gap-2" data-module="account-form" data-action="{{ url('/api/v1/account/tickets/'.$ticket['public_id'].'/messages') }}" data-method="POST" data-success="reload" novalidate>
                        <label for="ticket-reply" class="field-label">{{ __('account.tickets.reply') }}</label>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
                            <textarea id="ticket-reply" name="body" rows="2" maxlength="10000" class="field-input py-2.5" placeholder="{{ __('account.tickets.reply_placeholder') }}" aria-describedby="ticket-reply-error" required></textarea>
                            <button type="submit" class="btn-primary shrink-0"><x-store.icon name="send" :size="18" /> {{ __('account.tickets.send') }}</button>
                        </div>
                        <p id="ticket-reply-error" class="field-error" data-error-for="body" hidden></p>
                        <div class="alert-danger" data-form-error hidden role="alert"><p data-form-error-text></p></div>
                    </form>
                @endif
                <p class="text-xs text-content-muted">{{ __('account.tickets.no_attachments') }}</p>
            </section>
        @elseif ($tickets !== [])
            <div class="hidden rounded-xl border border-dashed border-line-default p-8 text-center text-sm text-content-muted lg:block">{{ __('account.tickets.pick') }}</div>
        @endif
    </div>
@endsection
