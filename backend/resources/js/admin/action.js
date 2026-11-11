import { request } from '../lib/http.js';
import { busy, messageFor, toast } from '../lib/forms.js';
import { confirmDialog } from './dialog.js';

/**
 * One-click admin actions (M14): every `[data-action-url]` button inside the module element
 * calls its JSON route (`data-action-method`, default POST) after the confirmation dialog
 * (`data-confirm-title`, `data-confirm-text`, `data-confirm-danger`). With `data-reason`
 * (its label) the dialog asks for a written reason, sent as `data-reason-field` (default
 * `reason`). Extra JSON fields come from `data-action-body`. Reloads after success unless
 * `data-no-reload`.
 */
export default function adminAction(root) {
    const buttons = root.matches('[data-action-url]') ? [root] : Array.from(root.querySelectorAll('[data-action-url]'));

    buttons.forEach((button) => {
        button.addEventListener('click', async () => {
            const { confirmed, reason } = await confirmDialog({
                title: button.dataset.confirmTitle ?? button.textContent.trim(),
                text: button.dataset.confirmText ?? '',
                confirmLabel: button.dataset.confirmLabel,
                danger: button.dataset.confirmDanger !== undefined,
                reason: button.dataset.reason ? { label: button.dataset.reason, min: Number(button.dataset.reasonMin ?? 5) } : null,
            });

            if (!confirmed) {
                return;
            }

            const body = button.dataset.actionBody ? JSON.parse(button.dataset.actionBody) : {};

            if (button.dataset.reason) {
                body[button.dataset.reasonField ?? 'reason'] = reason;
            }

            const method = (button.dataset.actionMethod ?? 'POST').toUpperCase();
            const result = await busy(button, () => request(method, button.dataset.actionUrl, method === 'GET' ? {} : { body }));

            if (!result.ok) {
                const fieldErrors = Object.values(result.body?.errors ?? {}).flat();
                toast(fieldErrors[0] ?? messageFor(result));

                return;
            }

            toast(result.body?.message ?? '');

            if (button.dataset.noReload === undefined) {
                window.setTimeout(() => window.location.reload(), 600);
            }
        });
    });
}
