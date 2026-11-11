{{-- Affiliate edit panel (M15): write-only payout details and the commissions with approval. --}}
<x-admin.card :title="__('admin.affiliates.payout')">
    <p class="flex items-center justify-between gap-3 text-sm">
        <span>{{ __('admin.affiliates.payout_details') }}</span>
        <x-admin.badge :tone="$panel['payout_configured'] ? 'success' : 'neutral'" :dot="false">{{ $panel['payout_configured'] ? __('admin.common.configured') : __('admin.common.not_configured') }}</x-admin.badge>
    </p>
    <p class="mt-2 text-xs text-content-muted">{{ __('admin.common.write_only') }}</p>
    @if ($canManage)
        <details class="mt-3">
            <summary class="cursor-pointer text-sm font-medium text-accent-text">{{ __('admin.affiliates.replace_payout') }}</summary>
            <form class="mt-3 flex flex-col gap-3" data-module="form" data-action="{{ $screen->apiUrl($record) }}" data-method="PATCH" data-success="reload" autocomplete="off" novalidate>
                <x-admin.field name="payout_details[method]" id="ap-method" :label="__('admin.fields.payout_method')" maxlength="32" />
                <x-admin.field name="payout_details[account]" id="ap-account" type="password" :label="__('admin.fields.payout_account')" maxlength="190" autocomplete="new-password" dir="ltr" />
                <x-admin.field name="payout_details[notes]" id="ap-notes" :label="__('admin.fields.payout_notes')" maxlength="500" />
                <x-admin.form-alerts />
                <div><button type="submit" class="admin-btn-primary">{{ __('admin.common.save') }}</button></div>
            </form>
        </details>
    @endif
</x-admin.card>
<x-admin.card :title="__('admin.affiliates.commissions')" :padded="false">
    <x-admin.table :table="$panel['commissions']" :title="__('admin.affiliates.commissions')" :actions="true" class="border-0 shadow-none" />
</x-admin.card>
