import { request } from '../lib/http.js';
import { busy, clearErrors, formValues, showErrors, toast } from '../lib/forms.js';
import { confirmDialog } from './dialog.js';

/**
 * Admin JSON forms (M14). The named fields go as JSON to `data-action` with `data-method`; the
 * envelope's field errors appear beside the fields (`[data-error-for]`, dot keys such as
 * `credentials.secret_key`) and other errors in `[data-form-error]`. Optional `data-confirm`
 * (with `data-confirm-danger`) asks first. After success: `data-success="reload"` reloads, else
 * the message shows in `[data-form-success]` or a toast and `data-reset` clears the form.
 * Checkboxes with `data-boolean` send true/false; fields with `data-nullable` send null when
 * empty.
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

        const body = formValues(form);

        form.querySelectorAll('[data-nullable]').forEach((field) => {
            if (field.value === '') {
                setPath(body, field.name, null);
            }
        });

        const method = (form.dataset.method ?? 'POST').toUpperCase();
        const result = await busy(form.querySelector('[type="submit"]'), () => request(method, form.dataset.action, { body }));

        if (!result.ok) {
            showErrors(form, result);

            return;
        }

        if (form.dataset.success === 'reload') {
            window.location.reload();

            return;
        }

        if (form.dataset.reset !== undefined) {
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

function setPath(target, name, value) {
    const path = name.replace(/\]/g, '').split('[');
    let node = target;

    path.slice(0, -1).forEach((part) => {
        node[part] ??= {};
        node = node[part];
    });

    node[path[path.length - 1]] = value;
}
