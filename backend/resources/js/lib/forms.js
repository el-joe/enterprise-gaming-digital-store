import { t } from './config.js';

/**
 * Field errors from the JSON error envelope (API_DOCUMENTATION.md, field mapping rule): each
 * `errors` key in dot notation goes to the `[data-error-for="key"]` element inside `root`, its
 * field gets `aria-invalid`, the first invalid field is focused, and errors without a field (or
 * the envelope message) go to the form-level `[data-form-error]` alert.
 */
export function clearErrors(root) {
    root.querySelectorAll('[data-error-for]').forEach((element) => {
        element.hidden = true;
        element.textContent = '';
    });
    root.querySelectorAll('[aria-invalid="true"]').forEach((element) => element.removeAttribute('aria-invalid'));
    showFormError(root, null);
}

export function showErrors(root, result) {
    clearErrors(root);

    const body = result?.body ?? null;
    const errors = body?.errors ?? {};
    const unmatched = [];
    let first = null;

    Object.entries(errors).forEach(([key, messages]) => {
        const target = root.querySelector(`[data-error-for="${CSS.escape(key)}"]`);
        const message = Array.isArray(messages) ? messages[0] : String(messages);

        if (!target) {
            unmatched.push(message);

            return;
        }

        target.textContent = message;
        target.hidden = false;

        const field = root.querySelector(`[name="${CSS.escape(fieldName(key))}"]`);

        if (field) {
            field.setAttribute('aria-invalid', 'true');
            first ??= field;
        }
    });

    if (unmatched.length > 0 || Object.keys(errors).length === 0) {
        showFormError(root, unmatched[0] ?? messageFor(result));
    }

    first?.focus();
}

/** `a.0.b` to the form field name `a[0][b]`. */
export function fieldName(key) {
    const [head, ...rest] = key.split('.');

    return head + rest.map((part) => `[${part}]`).join('');
}

export function showFormError(root, message) {
    const alert = root.querySelector('[data-form-error]');

    if (!alert) {
        return;
    }

    alert.hidden = !message;
    const text = alert.querySelector('[data-form-error-text]');

    if (text) {
        text.textContent = message ?? '';
    }
}

export function messageFor(result) {
    if (!result || result.status === 0) {
        return t('network_error');
    }

    if (result.status === 419) {
        return t('session_expired');
    }

    if (result.status === 429) {
        return result.body?.message ?? t('rate_limited');
    }

    return result.body?.message ?? t('generic_error');
}

/** Disable a button while a request runs and mark it busy. */
export async function busy(button, task) {
    if (!button) {
        return task();
    }

    button.disabled = true;
    button.setAttribute('aria-busy', 'true');

    try {
        return await task();
    } finally {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    }
}

/** Plain object of a form's named controls (checkbox: boolean; radio: the checked one; `a[b]` nested; `a[]` a list of the checked values, M14). */
export function formValues(form) {
    const values = {};

    // A <form> also owns controls outside it that name it with `form="id"` (M15 permission matrix).
    const fields = form instanceof HTMLFormElement
        ? Array.from(form.elements).filter((field) => field.name && ['INPUT', 'SELECT', 'TEXTAREA'].includes(field.tagName))
        : Array.from(form.querySelectorAll('input[name], select[name], textarea[name]'));

    fields.forEach((field) => {
        if (field.type === 'submit' || field.disabled || (field.type === 'radio' && !field.checked)) {
            return;
        }

        if (field.name.endsWith('[]')) {
            const listPath = field.name.slice(0, -2).replace(/\]/g, '').split('[');
            let parent = values;

            listPath.slice(0, -1).forEach((part) => {
                parent[part] ??= {};
                parent = parent[part];
            });

            const key = listPath[listPath.length - 1];
            parent[key] ??= [];

            // `data-list-sentinel` only makes sure an empty list is still sent (M15).
            if (field.dataset.listSentinel === undefined && (!['checkbox', 'radio'].includes(field.type) || field.checked)) {
                parent[key].push(field.value);
            }

            return;
        }

        const value = field.type === 'checkbox' ? field.checked : field.value;
        const path = field.name.replace(/\]/g, '').split('[');
        let target = values;

        path.slice(0, -1).forEach((part) => {
            target[part] ??= {};
            target = target[part];
        });

        target[path[path.length - 1]] = value;
    });

    return values;
}

export function toast(message) {
    const region = document.querySelector('[data-toast-region]');

    if (!region) {
        return;
    }

    const note = document.createElement('p');
    note.className = 'pointer-events-auto rounded-md border border-line-default bg-surface-overlay px-4 py-3 text-sm shadow-raised';
    note.textContent = message;
    region.replaceChildren(note);
    window.setTimeout(() => note.remove(), 4000);
}
