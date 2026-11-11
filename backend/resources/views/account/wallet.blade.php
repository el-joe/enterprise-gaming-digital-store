@extends('layouts.account')

@section('title', __('account.wallet.title'))
@section('nav', 'wallet')

@section('page')
    <x-account.page-head :title="__('account.wallet.title')" :subtitle="__('account.wallet.credits_only')" />

    <div class="grid gap-4 md:grid-cols-[minmax(0,320px)_minmax(0,1fr)]">
        <div class="rounded-xl rounded-se-[28px] border border-accent-primary bg-accent-tint p-5">
            <p class="text-xs font-medium text-accent-text">{{ __('account.wallet.available') }}</p>
            <p class="mt-1 font-display text-[40px] leading-[48px] font-bold" dir="ltr">{{ $wallet['balance']['formatted'] }}</p>
            <p class="mt-2 text-xs text-content-secondary">{{ __('account.wallet.no_top_up') }}</p>
            @if ($wallet['status'] === 'frozen')
                <p class="mt-3"><x-account.badge tone="danger">{{ __('account.wallet.frozen') }}</x-account.badge></p>
            @endif
        </div>
        <div class="rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-5">
            <h2 class="font-semibold">{{ __('account.wallet.how_title') }}</h2>
            <p class="mt-2 text-sm text-content-secondary">{{ __('account.wallet.how_text') }}</p>
            <a href="{{ url()->localized('storefront.account.tickets.create') }}" class="mt-3 inline-block text-xs font-medium text-accent-text">{{ __('account.wallet.questions') }}</a>
        </div>
    </div>

    <section aria-labelledby="transactions-title" class="flex flex-col gap-3">
        <h2 id="transactions-title" class="text-xl font-semibold">{{ __('account.wallet.transactions') }}</h2>
        @if ($transactions === [])
            <p class="rounded-xl border border-line-subtle bg-surface-raised p-5 text-sm text-content-secondary">{{ __('account.wallet.empty') }}</p>
        @else
            <div class="overflow-hidden rounded-xl border border-line-subtle bg-surface-raised">
                <table class="w-full text-sm">
                    <thead class="bg-surface-overlay/60 text-xs text-content-secondary">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-start font-medium">{{ __('account.orders.date') }}</th>
                            <th scope="col" class="px-4 py-3 text-start font-medium">{{ __('account.wallet.description') }}</th>
                            <th scope="col" class="hidden px-4 py-3 text-start font-medium sm:table-cell">{{ __('account.wallet.type') }}</th>
                            <th scope="col" class="px-4 py-3 text-end font-medium">{{ __('account.wallet.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-subtle">
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap"><x-account.time :at="$transaction['created_at']" /></td>
                                <td class="px-4 py-3">
                                    {{ __('account.wallet.types.'.$transaction['type']) }}
                                    @if ($transaction['note'])<span class="block text-xs text-content-muted">{{ $transaction['note'] }}</span>@endif
                                </td>
                                <td class="hidden px-4 py-3 sm:table-cell"><x-account.badge :tone="$transaction['credit'] ? 'success' : 'neutral'" :dot="false">{{ $transaction['credit'] ? __('account.wallet.credit') : __('account.wallet.debit') }}</x-account.badge></td>
                                <td @class(['px-4 py-3 text-end font-medium whitespace-nowrap', 'text-status-success' => $transaction['credit']]) dir="ltr">{{ $transaction['credit'] ? '+' : '−' }}{{ $transaction['amount'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('account.partials.pagination', ['paginator' => $paginator])
        @endif
    </section>
@endsection
