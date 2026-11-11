import { t } from '../lib/config.js';
import { post } from '../lib/http.js';
import { busy, messageFor } from '../lib/forms.js';

/**
 * Secure code reveal (M13, `<x-store.code-reveal>`; API 4a; owner decision 7).
 *
 * 1. First reveal of a unit: confirm intent (a revealed code cannot be returned).
 * 2. The server answers 423 `recent_auth_required`: the customer re-enters the password
 *    (`POST /api/v1/account/confirm-password`, valid 15 minutes) and the reveal is retried.
 *    Guests prove access with the signed link and the email code instead (no password step).
 * 3. The code, PIN, redemption link and instructions are written into the dialog only now,
 *    as text. They are never kept in browser storage or data attributes, and they are wiped when the
 *    dialog closes, after `data-hide-after` seconds, or as soon as the tab is hidden.
 *    Copy puts the code on the clipboard and clears it after `data-clipboard-clear-after`
 *    seconds where the browser allows it (retried on the next focus).
 * 4. 429: the reveal limit; the customer is pointed to support.
 */
export default function reveal(root) {
    const dialog = root.querySelector('[data-reveal-dialog]');
    const title = dialog.querySelector('[data-reveal-title]');
    const errorBox = root.querySelector('[data-reveal-error]');
    const passwordForm = dialog.querySelector('[data-step="password"]');
    const passwordInput = passwordForm.querySelector('[data-password-input]');
    const passwordError = passwordForm.querySelector('[data-error-for="password"]');
    const codeValue = dialog.querySelector('[data-code-value]');
    const pinRow = dialog.querySelector('[data-pin-row]');
    const pinValue = dialog.querySelector('[data-pin-value]');
    const redeem = dialog.querySelector('[data-redeem-link]');
    const instructions = dialog.querySelector('[data-instructions]');
    const countdown = dialog.querySelector('[data-countdown]');
    const copyButton = dialog.querySelector('[data-reveal-copy]');
    const copyLabel = copyButton.querySelector('[data-copy-label]');
    const guest = root.dataset.guest === '1';
    const hideAfter = Math.max(1, Number(root.dataset.hideAfter) || 60);
    const clearAfter = Math.max(1, Number(root.dataset.clipboardClearAfter) || 60);

    let current = null;
    let hideTimer = null;
    let tickTimer = null;
    let clipboardTimer = null;
    let clipboardPending = false;

    const showError = (message) => {
        errorBox.textContent = message ?? '';
        errorBox.hidden = !message;
    };

    const step = (name) => {
        dialog.querySelectorAll('[data-step]').forEach((section) => {
            section.hidden = section.dataset.step !== name;

            if (section.dataset.step === name) {
                title.textContent = section.dataset.title;
            }
        });

        if (!dialog.open) {
            dialog.showModal();
        }
    };

    /** Remove every trace of a revealed code from the page. */
    const wipe = () => {
        window.clearTimeout(hideTimer);
        window.clearInterval(tickTimer);
        hideTimer = null;
        tickTimer = null;
        codeValue.textContent = '';
        pinValue.textContent = '';
        pinRow.hidden = true;
        redeem.removeAttribute('href');
        redeem.hidden = true;
        instructions.textContent = '';
        instructions.hidden = true;
        countdown.textContent = '';
        copyLabel.textContent = t('reveal_copy');
        passwordInput.value = '';
        passwordError.hidden = true;
    };

    const close = () => {
        wipe();

        if (dialog.open) {
            dialog.close();
        }
    };

    const clearClipboard = async () => {
        try {
            await navigator.clipboard.writeText('');
            clipboardPending = false;
        } catch {
            clipboardPending = true;
        }
    };

    const urlFor = (button) => button.dataset.revealUrl ?? root.dataset.revealUrl.replace('__unit__', button.dataset.reveal);

    const display = (data) => {
        codeValue.textContent = data.code ?? '';
        pinRow.hidden = !data.pin;
        pinValue.textContent = data.pin ?? '';

        if (typeof data.redemption_url === 'string' && /^https:\/\//i.test(data.redemption_url)) {
            redeem.href = data.redemption_url;
            redeem.hidden = false;
        }

        instructions.textContent = data.instructions ?? '';
        instructions.hidden = !data.instructions;
        step('code');

        let remaining = hideAfter;
        countdown.textContent = t('reveal_countdown', { seconds: remaining });
        tickTimer = window.setInterval(() => {
            remaining -= 1;
            countdown.textContent = t('reveal_countdown', { seconds: Math.max(0, remaining) });
        }, 1000);
        hideTimer = window.setTimeout(close, hideAfter * 1000);

        if (current) {
            current.dataset.sealed = '0';
        }
    };

    const request = async () => {
        if (!current) {
            return;
        }

        showError(null);
        const result = await busy(current, () => post(urlFor(current), {}));

        if (result.ok) {
            display(result.body?.data ?? {});

            return;
        }

        if (result.status === 423 && !guest) {
            step('password');
            passwordInput.focus();

            return;
        }

        if (result.status === 429) {
            step('limited');

            return;
        }

        close();
        showError(messageFor(result));
    };

    root.querySelectorAll('[data-reveal]').forEach((button) => {
        button.addEventListener('click', () => {
            current = button;

            if (button.dataset.sealed === '1') {
                step('intent');
                dialog.querySelector('[data-reveal-continue]').focus();

                return;
            }

            request();
        });
    });

    dialog.querySelector('[data-reveal-continue]').addEventListener('click', () => request());

    passwordForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        passwordError.hidden = true;
        passwordInput.removeAttribute('aria-invalid');

        const result = await busy(passwordForm.querySelector('[type="submit"]'), () => post(root.dataset.confirmUrl, { password: passwordInput.value }));

        if (!result.ok) {
            passwordError.textContent = result.body?.errors?.password?.[0] ?? messageFor(result);
            passwordError.hidden = false;
            passwordInput.setAttribute('aria-invalid', 'true');
            passwordInput.focus();

            return;
        }

        passwordInput.value = '';
        request();
    });

    copyButton.addEventListener('click', async () => {
        const code = codeValue.textContent;

        if (!code) {
            return;
        }

        try {
            await navigator.clipboard.writeText(code);
            copyLabel.textContent = copyButton.dataset.copied;
            window.clearTimeout(clipboardTimer);
            clipboardTimer = window.setTimeout(clearClipboard, clearAfter * 1000);
        } catch {
            const selection = window.getSelection();
            const range = document.createRange();
            range.selectNodeContents(codeValue);
            selection.removeAllRanges();
            selection.addRange(range);
        }
    });

    dialog.querySelectorAll('[data-reveal-close]').forEach((button) => button.addEventListener('click', close));
    dialog.addEventListener('close', wipe);
    dialog.addEventListener('cancel', wipe);

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            close();
        }
    });
    window.addEventListener('pagehide', close);
    window.addEventListener('focus', () => {
        if (clipboardPending) {
            clearClipboard();
        }
    });
}
