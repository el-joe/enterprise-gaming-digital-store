import { config, t } from '../lib/config.js';
import { request } from '../lib/http.js';
import { busy, clearErrors, formValues, showErrors, toast } from '../lib/forms.js';
import { confirmDialog } from './dialog.js';

/**
 * Admin JSON forms (M14, M15). The named fields go as JSON to `data-action` with `data-method`;
 * the envelope's field errors appear beside the fields (`[data-error-for]`, dot keys such as
 * `credentials.secret_key`) and other errors in `[data-form-error]`. Optional `data-confirm`
 * (with `data-confirm-danger`) asks first.
 *
 * After success:
 * - `data-success="reload"` reloads; `data-success="redirect"` goes to `data-redirect`;
 * - `data-success="reveal"` writes the response fields named by `[data-reveal-field]` into the
 *   `[data-reveal]` panel once (a new token or secret), clears the form and never stores them;
 * - otherwise the message shows in `[data-form-success]` or a toast, and `data-reset` clears it.
 *
 * Fields with `data-nullable` send null when empty; `data-json` textareas are parsed as JSON
 * (an error beside the field when they do not parse; empty sends null); `data-csv` inputs send
 * a list of the comma-separated values; `data-rows` groups send a list of objects (rowValues). A
 * form with
 * `data-multipart` sends multipart form data (file uploads) with the CSRF header.
 */
export default function adminForm(form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(form);
        form.querySelector('[data-form-success]')?.setAttribute('hidden', '');

        if (form.dataset.confirm) {
            const { confirmed } = await confirmDialog({ title: form.dataset.confirm, text: form.dataset.confirmText ?? '', danger: form.dataset.confirmDanger !== undefined });

            if (!confirmed) {
                return;
            }
        }

        const method = (form.dataset.method ?? 'POST').toUpperCase();
        const submit = form.querySelector('[type="submit"]');
        let result;

        if (form.dataset.multipart !== undefined) {
            result = await busy(submit, () => upload(form, method));
        } else {
            const body = formValues(form);

            form.querySelectorAll('[data-nullable]').forEach((field) => {
                if (field.value === '' && !field.disabled) {
                    setPath(body, field.name, null);
                }
            });

            form.querySelectorAll('[data-rows]').forEach((group) => {
                body[group.dataset.rows] = rowValues(group);
            });

            form.querySelectorAll('[data-csv]').forEach((field) => {
                if (!field.disabled) {
                    setPath(body, field.name, field.value.split(',').map((item) => item.trim()).filter((item) => item !== ''));
                }
            });

            const jsonErrors = {};

            form.querySelectorAll('[data-json]').forEach((field) => {
                if (field.disabled) {
                    return;
                }

                if (field.value.trim() === '') {
                    setPath(body, field.name, null);

                    return;
                }

                try {
                    setPath(body, field.name, JSON.parse(field.value));
                } catch {
                    jsonErrors[field.name.replace(/\]\[/g, '.').replace(/\[/g, '.').replace(/\]/g, '')] = [t('json_invalid')];
                }
            });

            if (Object.keys(jsonErrors).length > 0) {
                showErrors(form, { status: 422, body: { errors: jsonErrors } });

                return;
            }

            result = await busy(submit, () => request(method, actionUrl(form, body), { body }));
        }

        if (!result.ok) {
            showErrors(form, result);

            return;
        }

        if (form.dataset.success === 'reload') {
            window.location.reload();

            return;
        }

        if (form.dataset.success === 'redirect' && form.dataset.redirect) {
            window.location.assign(form.dataset.redirect);

            return;
        }

        if (form.dataset.success === 'reveal') {
            reveal(form, result.body?.data ?? {});
        }

        if (form.dataset.reset !== undefined || form.dataset.success === 'reveal') {
            form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), textarea').forEach((field) => {
                field.value = '';
            });
        }

        const success = form.querySelector('[data-form-success]');
        const message = result.body?.message ?? '';

        if (success) {
            success.querySelector('[data-form-success-text]').textContent = message;
            success.hidden = false;
        } else {
            toast(message);
        }
    });
}

/**
 * A list of objects from `[data-row]` elements (M15): rows whose `[data-row-enabled]` checkbox is
 * off are skipped; each `[data-row-field]` control gives one property (checkbox: boolean).
 */
function rowValues(group) {
    return Array.from(group.querySelectorAll('[data-row]'))
        .filter((row) => row.querySelector('[data-row-enabled]')?.checked ?? true)
        .map((row) => {
            const item = {};

            row.querySelectorAll('[data-row-field]').forEach((field) => {
                item[field.dataset.rowField] = field.type === 'checkbox' ? field.checked : field.value;
            });

            return item;
        });
}

/** `data-action` with `__field__` placeholders filled from the form values (M15). */
function actionUrl(form, body) {
    return form.dataset.action.replace(/__([a-z_]+)__/g, (match, key) => encodeURIComponent(String(body[key] ?? '')));
}

async function upload(form, method) {
    const data = new FormData();

    form.querySelectorAll('input[name], select[name], textarea[name]').forEach((field) => {
        if (field.disabled || field.type === 'submit') {
            return;
        }

        if (field.type === 'file') {
            Array.from(field.files ?? []).forEach((file) => data.append(field.name, file));
        } else if (field.type === 'checkbox') {
            data.append(field.name, field.checked ? '1' : '0');
        } else if (field.value !== '') {
            data.append(field.name, field.value);
        }
    });

    if (method !== 'POST') {
        data.append('_method', method);
    }

    try {
        const response = await fetch(form.dataset.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'Accept-Language': config().locale, 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': config().csrf },
            body: data,
        });
        let payload = null;

        try {
            payload = await response.json();
        } catch {
            payload = null;
        }

        return { ok: response.ok, status: response.status, body: payload };
    } catch {
        return { ok: false, status: 0, body: null };
    }
}

/** Show a value that the server returns once (a new token or secret); it is never stored here. */
function reveal(form, data) {
    const panel = document.querySelector(form.dataset.revealTarget ?? '[data-reveal]');

    if (!panel) {
        return;
    }

    panel.querySelectorAll('[data-reveal-field]').forEach((element) => {
        element.textContent = data[element.dataset.revealField] ?? '';
    });
    panel.hidden = false;
    panel.querySelector('[data-reveal-field]')?.focus?.();
}

function setPath(target, name, value) {
    const path = name.replace(/\]/g, '').split('[');
    let node = target;

    path.slice(0, -1).forEach((part) => {
        node[part] ??= {};
        node = node[part];
    });

    node[path[path.length - 1]] = value;
}
