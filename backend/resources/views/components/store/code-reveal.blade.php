{{--
    Secure code reveal (M13, Figma "Code Reveal Flow"; owner decision 7), shared by the account
    pages and the order result page. Wraps code units (<x-store.code-unit>) and holds one dialog:
    1 confirm intent (first reveal only), 2 password re-authentication (423 `recent_auth_required`),
    3 the code (copy, hide, redemption link), 4 rate limited (429). Codes are never in the page
    before a reveal, never stored in the browser, and hidden again after `hide-after` seconds or
    when the tab is left; a copied code is cleared from the clipboard after the same time where
    the browser allows it. `revealUrl` is the per-order template with `__unit__` (a unit button may
    carry its own `data-reveal-url`).
--}}
@props(['revealUrl' => null, 'guest' => false])

<div {{ $attributes }} data-module="reveal" data-guest="{{ $guest ? '1' : '0' }}"
    @if ($revealUrl) data-reveal-url="{{ $revealUrl }}" @endif
    data-confirm-url="{{ url('/api/v1/account/confirm-password') }}"
    data-hide-after="{{ (int) config('fulfillment.web_reveal_hide_seconds', 60) }}"
    data-clipboard-clear-after="{{ (int) config('fulfillment.web_reveal_hide_seconds', 60) }}">
    {{ $slot }}

    <p class="mt-3 text-sm text-status-danger" data-reveal-error role="alert" hidden></p>

    <dialog class="m-auto w-[min(92vw,420px)] rounded-xl rounded-se-[28px] border border-line-default bg-surface-raised p-0 text-content-primary shadow-overlay backdrop:bg-black/75" aria-labelledby="reveal-title" data-reveal-dialog>
        <div class="flex items-start justify-between gap-3 px-5 pt-5">
            <h2 id="reveal-title" class="text-lg font-semibold" data-reveal-title>{{ __('account.reveal.intent_title') }}</h2>
            <button type="button" class="-me-1 -mt-1 inline-flex size-9 items-center justify-center rounded-md text-content-muted hover:text-content-primary" data-reveal-close>
                <x-store.icon name="x" :label="__('account.common.close')" />
            </button>
        </div>

        <div class="flex flex-col gap-4 p-5" data-step="intent" data-title="{{ __('account.reveal.intent_title') }}">
            <p class="text-sm text-content-secondary">{{ __('account.reveal.intent_text') }}</p>
            <div class="alert-warning">
                <x-store.icon name="alert" class="mt-0.5 text-status-warning" />
                <p>{{ __('account.reveal.intent_note') }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" class="btn-secondary" data-reveal-close>{{ __('account.common.cancel') }}</button>
                <button type="button" class="btn-primary" data-reveal-continue>{{ __('account.reveal.continue') }}</button>
            </div>
        </div>

        <form class="flex flex-col gap-4 p-5" data-step="password" data-title="{{ __('account.reveal.password_title') }}" hidden novalidate>
            <p class="text-sm text-content-secondary">{{ __('account.reveal.password_text') }}</p>
            <div>
                <label for="reveal-password" class="field-label">{{ __('account.fields.password') }}</label>
                <input id="reveal-password" name="password" type="password" autocomplete="current-password" class="field-input" aria-describedby="reveal-password-error" required data-password-input>
                <p id="reveal-password-error" class="field-error" data-error-for="password" hidden></p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" class="btn-secondary" data-reveal-close>{{ __('account.common.cancel') }}</button>
                <button type="submit" class="btn-primary">{{ __('account.reveal.confirm') }}</button>
            </div>
        </form>

        <div class="flex flex-col gap-4 p-5" data-step="code" data-title="{{ __('account.reveal.code_title') }}" hidden>
            <div class="rounded-md border border-accent-primary bg-surface-sunken px-4 py-3 text-center">
                <p class="font-mono text-lg font-medium tracking-[0.08em] break-all text-accent-text select-all md:text-xl" dir="ltr" data-code-value></p>
                <p class="mt-2 font-mono text-sm text-content-secondary" dir="ltr" data-pin-row hidden>{{ __('account.reveal.pin') }} <span class="text-content-primary" data-pin-value></span></p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" class="btn-primary" data-reveal-copy data-copied="{{ __('account.reveal.copied') }}"><x-store.icon name="copy" :size="18" /> <span data-copy-label>{{ __('account.reveal.copy') }}</span></button>
                <button type="button" class="btn-secondary" data-reveal-close><x-store.icon name="eye-off" :size="18" /> {{ __('account.reveal.hide') }}</button>
            </div>
            <a class="btn-ghost min-h-9 justify-start px-0 text-accent-text" target="_blank" rel="noopener noreferrer" data-redeem-link hidden><x-store.icon name="external" :size="16" /> {{ __('account.reveal.redeem') }}</a>
            <p class="text-xs whitespace-pre-line text-content-secondary" data-instructions hidden></p>
            <p class="text-center text-xs text-content-muted" aria-live="off" data-countdown></p>
        </div>

        <div class="flex flex-col items-center gap-4 p-5 text-center" data-step="limited" data-title="{{ __('account.reveal.limited_title') }}" hidden>
            <span class="inline-flex size-12 items-center justify-center rounded-full bg-status-danger-tint text-status-danger"><x-store.icon name="lock" /></span>
            <p class="text-sm text-content-secondary" data-limited-text>{{ __('account.reveal.limited_text') }}</p>
            @unless ($guest)
                <a href="{{ url()->localized('storefront.account.tickets.create') }}" class="btn-secondary w-full"><x-store.icon name="chat" :size="18" /> {{ __('account.reveal.contact_support') }}</a>
            @endunless
        </div>
    </dialog>
</div>
