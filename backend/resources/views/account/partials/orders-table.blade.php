{{--
    Order rows (Figma "Orders Table"): a table from md up, stacked cards below. Totals are the
    server-formatted display totals.
--}}
@php($itemsLabel = static fn (array $order): string => $order['first_item'] === null ? '' : $order['first_item']['name'].($order['first_item']['quantity'] > 1 ? ' × '.$order['first_item']['quantity'] : '').($order['other_items'] > 0 ? ' '.trans_choice('account.orders.more_items', $order['other_items'], ['count' => $order['other_items']]) : ''))

<div class="hidden overflow-hidden rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised md:block">
    <table class="w-full text-sm">
        <thead class="bg-surface-overlay/60 text-start text-xs font-medium text-content-secondary">
            <tr>
                <th scope="col" class="px-4 py-3 text-start font-medium">{{ __('account.orders.order') }}</th>
                <th scope="col" class="px-4 py-3 text-start font-medium">{{ __('account.orders.date') }}</th>
                <th scope="col" class="px-4 py-3 text-start font-medium">{{ __('account.orders.items') }}</th>
                <th scope="col" class="px-4 py-3 text-start font-medium">{{ __('account.orders.status') }}</th>
                <th scope="col" class="px-4 py-3 text-end font-medium">{{ __('account.orders.total') }}</th>
                <th scope="col" class="px-4 py-3"><span class="sr-only">{{ __('account.orders.view') }}</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line-subtle">
            @foreach ($orders as $order)
                <tr>
                    <td class="px-4 py-3 font-mono text-xs text-content-secondary" dir="ltr">{{ $order['number'] }}</td>
                    <td class="px-4 py-3 whitespace-nowrap"><x-account.time :at="$order['created_at']" /></td>
                    <td class="w-full max-w-0 px-4 py-3"><span class="block truncate">{{ $itemsLabel($order) }}</span></td>
                    <td class="px-4 py-3"><x-account.status :status="$order['status']" /></td>
                    <td class="px-4 py-3 text-end font-medium whitespace-nowrap" dir="ltr">{{ $order['totals']['display']['formatted'] }}</td>
                    <td class="px-4 py-3 text-end">
                        <a href="{{ url()->localized('storefront.account.orders.show', ['orderPublicId' => $order['public_id']]) }}" class="btn-secondary min-h-9 px-3.5 text-xs">{{ __('account.orders.view') }}<span class="sr-only"> {{ $order['number'] }}</span></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<ul class="flex flex-col gap-3 md:hidden">
    @foreach ($orders as $order)
        <li class="relative rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4">
            <div class="flex items-center justify-between gap-3">
                <span class="font-mono text-sm" dir="ltr">{{ $order['number'] }}</span>
                <x-account.status :status="$order['status']" />
            </div>
            <p class="mt-2 truncate text-sm font-semibold">
                <a href="{{ url()->localized('storefront.account.orders.show', ['orderPublicId' => $order['public_id']]) }}" class="after:absolute after:inset-0">{{ $itemsLabel($order) ?: $order['number'] }}</a>
            </p>
            <div class="mt-2 flex items-center justify-between gap-3 text-xs text-content-muted">
                <x-account.time :at="$order['created_at']" />
                <span class="text-base font-bold text-content-primary" dir="ltr">{{ $order['totals']['display']['formatted'] }}</span>
            </div>
        </li>
    @endforeach
</ul>
