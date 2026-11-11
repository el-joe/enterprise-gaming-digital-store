import { request } from '../lib/http.js';
import { busy, clearErrors, formValues, showErrors, toast } from '../lib/forms.js';

/**
 * Account forms (M13): the form's named fields go as JSON to `data-action` with `data-method`
 * (POST, PUT, PATCH, DELETE) on the existing account routes, with the session cookie and CSRF
 * token. Envelope errors appear beside the fields (`[data-error-for]`).
 *
 * After success (`data-success`):
 * - `reload`: reload the page (the server renders the new state);
 * - `redirect`: go to `data-redirect`, where `__id__` is replaced by the response value at
 *   `data-redirect-key` (dot path in `data`);
 * - `message` (default): show the envelope message in `[data-form-success]` (or a toast) and,
 *   with `data-reset`, clear the form.
 *
 * Fields with `data-nullable` send null instead of an empty string; `data-omit-empty` fields are
 * left out when empty.
 */
export default function accountForm(form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(form);

        const success = form.querySelector('[data-form-success]');

        if (success) {
            success.hidden = true;
        }

        const body = formValues(form);

        form.querySelectorAll('[data-nullable]').forEach((field) => {
            if (field.value === '' && field.name in body) {
                body[field.name] = null;
            }
        });

        form.querySelectorAll('[data-omit-empty]').forEach((field) => {
            if (field.value === '') {
                delete body[field.name];
            }
        });

        const method = (form.dataset.method ?? 'POST').toUpperCase();
        const submit = form.querySelector('[type="submit"]');
        const result = await busy(submit, () => request(method, form.dataset.action, { body }));

        if (!result.ok) {
            showErrors(form, result);

            return;
        }

        const mode = form.dataset.success ?? 'message';

        if (mode === 'reload') {
            window.location.reload();

            return;
        }

        if (mode === 'redirect') {
            const value = (form.dataset.redirectKey ?? '').split('.').filter(Boolean).reduce((data, key) => data?.[key], result.body?.data);
            window.location.assign(form.dataset.redirect.replace('__id__', encodeURIComponent(value ?? '')));

            return;
        }

        if (form.hasAttribute('data-reset')) {
            form.reset();
        }

        const message = result.body?.message ?? '';

        if (success) {
            success.querySelector('[data-form-success-text]').textContent = message;
            success.hidden = false;
        } else {
            toast(message);
        }
    });
}
