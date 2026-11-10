import { t } from '../lib/config.js';
import { post } from '../lib/http.js';
import { messageFor } from '../lib/forms.js';

const HIDE_AFTER_MS = 60000;

/**
 * Code reveal on the order page (API 4a). Customers re-enter their password when the server asks
 * (423 `recent_auth_required`); guests need the signed link and the email code in this session.
 * A revealed code is shown in the page only (never stored), hidden again after 60 seconds or
 * when the tab is left (owner decision 7), and every attempt is audited by the server.
 */
export default function reveal(root) {
    const prompt = root.querySelector('[data-password-prompt]');
    const errorBox = root.querySelector('[data-reveal-error]');
    const guest = root.dataset.guest === '1';
    let pending = null;
    const shown = new Map();

    const error = (message) => {
        errorBox.textContent = message ?? '';
        errorBox.hidden = !message;
    };

    const hide = (unit) => {
        const entry = shown.get(unit);

        if (!entry) {
            return;
        }

        window.clearTimeout(entry.timer);
        entry.slot.textContent = entry.masked;
        entry.panel.remove();
        entry.button.hidden = false;
        shown.delete(unit);
    };

    const display = (button, data) => {
        const row = button.closest('[data-unit]');
        const unit = row.dataset.unit;
        const slot = row.querySelector('[data-code-slot]') ?? document.createElement('span');
        const panel = document.createElement('div');
        panel.className = 'mt-2 flex w-full flex-col gap-1 rounded-md border border-accent-primary/60 bg-surface-raised p-3';

        const code = document.createElement('p');
        code.className = 'font-mono text-lg tracking-wider select-all';
        code.dir = 'ltr';
        code.textContent = data.code ?? '';
        panel.appendChild(code);

        if (data.pin) {
            const pin = document.createElement('p');
            pin.className = 'font-mono text-sm text-content-secondary';
            pin.dir = 'ltr';
            pin.textContent = `${t('pin')}: ${data.pin}`;
            panel.appendChild(pin);
        }

        if (data.instructions) {
            const help = document.createElement('p');
            help.className = 'text-xs text-content-secondary whitespace-pre-line';
            help.textContent = data.instructions;
            panel.appendChild(help);
        }

        const note = document.createElement('p');
        note.className = 'text-xs text-content-muted';
        note.textContent = t('code_hides');
        panel.appendChild(note);

        row.appendChild(panel);
        button.hidden = true;
        shown.set(unit, { slot, masked: slot.textContent, panel, button, timer: window.setTimeout(() => hide(unit), HIDE_AFTER_MS) });
    };

    const request = async (button) => {
        error(null);
        button.disabled = true;
        const result = await post(root.dataset.revealUrl.replace('__unit__', button.dataset.reveal), {});
        button.disabled = false;

        if (result.ok) {
            display(button, result.body?.data ?? {});

            return;
        }

        if (result.status === 423 && !guest) {
            pending = button;
            prompt.hidden = false;
            prompt.querySelector('[data-password-input]').focus();

            return;
        }

        error(messageFor(result));
    };

    root.querySelectorAll('[data-reveal]').forEach((button) => button.addEventListener('click', () => request(button)));

    prompt?.querySelector('[data-password-submit]').addEventListener('click', async () => {
        const input = prompt.querySelector('[data-password-input]');
        const fieldError = prompt.querySelector('[data-error-for="password"]');
        fieldError.hidden = true;
        const result = await post(root.dataset.confirmUrl, { password: input.value });

        if (!result.ok) {
            fieldError.textContent = result.body?.errors?.password?.[0] ?? messageFor(result);
            fieldError.hidden = false;

            return;
        }

        input.value = '';
        prompt.hidden = true;

        if (pending) {
            request(pending);
            pending = null;
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            Array.from(shown.keys()).forEach(hide);
        }
    });
}
