import { request } from '../lib/http.js';
import { busy, messageFor, toast } from '../lib/forms.js';

/**
 * One-click account actions (M13): each `[data-action-url]` button inside the module element
 * (or the element itself) calls its JSON route with `data-action-method` (default POST) and the
 * CSRF token. Optional: `data-action-confirm` (a confirmation question), `data-action-done` (a
 * toast on success), `data-action-reload` (reload the page), `data-action-remove` (the closest
 * matching ancestor is removed).
 */
export default function accountAction(root) {
    const buttons = root.matches('[data-action-url]') ? [root] : Array.from(root.querySelectorAll('[data-action-url]'));

    buttons.forEach((button) => {
        button.addEventListener('click', async () => {
            if (button.dataset.actionConfirm && !window.confirm(button.dataset.actionConfirm)) {
                return;
            }

            const method = (button.dataset.actionMethod ?? 'POST').toUpperCase();
            const result = await busy(button, () => request(method, button.dataset.actionUrl, method === 'POST' ? { body: {} } : {}));

            if (!result.ok) {
                toast(messageFor(result));

                return;
            }

            if (button.dataset.actionDone) {
                toast(button.dataset.actionDone);
            }

            if (button.hasAttribute('data-action-reload')) {
                window.location.reload();

                return;
            }

            if (button.dataset.actionRemove) {
                button.closest(button.dataset.actionRemove)?.remove();

                return;
            }

            if (button.dataset.actionDone) {
                button.disabled = true;
            }
        });
    });
}
