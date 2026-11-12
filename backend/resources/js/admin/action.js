import { request } from '../lib/http.js';
import { busy, messageFor, toast } from '../lib/forms.js';
import { confirmDialog } from './dialog.js';

/**
 * One-click admin actions (M14, M15): every `[data-action-url]` button inside the module element
 * calls its JSON route (`data-action-method`, default POST) after the confirmation dialog
 * (`data-confirm-title`, `data-confirm-text`, `data-confirm-danger`). With `data-reason`
 * (its label) the dialog asks for a written reason, sent as `data-reason-field` (default
 * `reason`). Extra JSON fields come from `data-action-body`, and from the inputs of the
 * `<template>` named by `data-confirm-fields`, shown in the dialog.
 *
 * After success: `data-redirect` goes there; inside a server-side table (rows drawn after the
 * page loaded, so clicks are delegated) the table redraws; otherwise the page reloads unless
 * `data-no-reload`.
 */
export default function adminAction(root) {
    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action-url]');

        if (!button || !root.contains(button) || button.disabled) {
            return;
        }

        event.preventDefault();
        run(root, button);
    });
}

async function run(root, button) {
    const method = (button.dataset.actionMethod ?? 'POST').toUpperCase();
    const send = (values, reason) => {
        const body = { ...(button.dataset.actionBody ? JSON.parse(button.dataset.actionBody) : {}), ...(values ?? {}) };

        if (button.dataset.reason) {
            body[button.dataset.reasonField ?? 'reason'] = reason;
        }

        return request(method, button.dataset.actionUrl, method === 'GET' ? {} : { body });
    };

    const outcome = await confirmDialog({
        title: button.dataset.confirmTitle ?? button.textContent.trim(),
        text: button.dataset.confirmText ?? '',
        confirmLabel: button.dataset.confirmLabel,
        danger: button.dataset.confirmDanger !== undefined,
        reason: button.dataset.reason ? { label: button.dataset.reason, min: Number(button.dataset.reasonMin ?? 5) } : null,
        fields: button.dataset.confirmFields ?? null,
        submit: button.dataset.confirmFields ? (values) => send(values, values.reason) : null,
    });

    if (!outcome.confirmed) {
        return;
    }

    const result = outcome.result ?? await busy(button, () => send(outcome.values, outcome.reason));

    if (!result.ok) {
        const fieldErrors = Object.values(result.body?.errors ?? {}).flat();
        toast(fieldErrors[0] ?? messageFor(result));

        return;
    }

    toast(result.body?.message ?? '');

    if (button.dataset.redirect) {
        window.setTimeout(() => window.location.assign(button.dataset.redirect), 400);

        return;
    }

    const table = button.closest('[data-module~="table"]');

    if (table) {
        table.dispatchEvent(new CustomEvent('admin:table-refresh'));

        return;
    }

    if (button.dataset.noReload === undefined) {
        window.setTimeout(() => window.location.reload(), 600);
    }
}
