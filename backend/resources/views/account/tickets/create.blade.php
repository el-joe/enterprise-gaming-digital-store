@extends('layouts.account')

@section('title', __('account.tickets.new'))
@section('nav', 'tickets')

@section('page')
    <div>
        <a href="{{ url()->localized('storefront.account.tickets') }}" class="inline-flex items-center gap-1 text-sm text-content-secondary hover:text-content-primary"><x-store.icon name="chevron-back" :size="16" /> {{ __('account.tickets.all') }}</a>
        <x-account.page-head class="mt-2" :title="__('account.tickets.new')" :subtitle="__('account.tickets.new_subtitle')" />
    </div>

    <form class="flex max-w-2xl flex-col gap-4 rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-6" novalidate
        data-module="account-form" data-action="{{ url('/api/v1/account/tickets') }}" data-method="POST"
        data-success="redirect" data-redirect="{{ url()->localized('storefront.account.tickets.show', ['ticketPublicId' => '__id__']) }}" data-redirect-key="ticket.public_id">
        @if ($order)
            <input type="hidden" name="order_public_id" value="{{ $order['public_id'] }}">
            <p class="text-sm text-content-secondary">{{ __('account.tickets.about_order') }} <span class="font-mono" dir="ltr">{{ $order['number'] }}</span></p>
        @endif
        <div>
            <label for="ticket-category" class="field-label">{{ __('account.tickets.category') }}</label>
            <select id="ticket-category" name="category" class="field-input" aria-describedby="ticket-category-error">
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected($order ? $category === 'order' : $category === 'other')>{{ __('account.tickets.categories.'.$category) }}</option>
                @endforeach
            </select>
            <p id="ticket-category-error" class="field-error" data-error-for="category" hidden></p>
        </div>
        <x-store.field name="subject" id="ticket-subject" :label="__('account.tickets.subject')" maxlength="190" required />
        <div>
            <label for="ticket-body" class="field-label">{{ __('account.tickets.body') }}</label>
            <textarea id="ticket-body" name="body" rows="6" maxlength="10000" class="field-input py-2.5" aria-describedby="ticket-body-error" required></textarea>
            <p id="ticket-body-error" class="field-error" data-error-for="body" hidden></p>
        </div>
        <p class="field-error" data-error-for="order_public_id" hidden></p>
        <p class="text-xs text-content-muted">{{ __('account.tickets.never_codes') }}</p>
        <div class="alert-danger" data-form-error hidden role="alert"><p data-form-error-text></p></div>
        <div><button type="submit" class="btn-primary"><x-store.icon name="send" :size="18" /> {{ __('account.tickets.open') }}</button></div>
    </form>
@endsection
