import { t } from '../lib/config.js';

/**
 * The confirmation dialog of the admin layout (`[data-confirm-dialog]`, a native <dialog>).
 * Resolves to `{ confirmed, reason }`. With `reason` the dialog asks for a written reason
 * (`min` characters) before the confirm button works; the reason goes to the audit log.
 * With `fields` (the id of a `<template>`), its inputs are shown in the dialog; `required` ones
 * must be filled, and the named values resolve as `values`.
 * With `submit(values)` (an async request returning `{ ok, status, body }`), the dialog stays
 * open while it runs and shows 422 errors beside the inputs (`[data-error-for="name"]`); it
 * resolves with that `result` once the request is not a validation failure.
 */
export function confirmDialog({ title, text = '', confirmLabel, danger = false, reason = null, fields = null, submit = null }) {
    const dialog = document.querySelector('[data-confirm-dialog]');

    if (!dialog) {
        return Promise.resolve({ confirmed: window.confirm(title), reason: '', values: {} });
    }

    const fieldsWrap = dialog.querySelector('[data-confirm-fields]');
    const fieldsError = dialog.querySelector('[data-confirm-fields-error]');
    const template = fields ? document.getElementById(fields) : null;
    fieldsWrap?.replaceChildren(template ? template.content.cloneNode(true) : '');

    if (fieldsWrap) {
        fieldsWrap.hidden = !template;
    }

    if (fieldsError) {
        fieldsError.hidden = true;
    }

    const fieldValues = () => Object.fromEntries([...(fieldsWrap?.querySelectorAll('[name]') ?? [])]
        .filter((input) => input.value !== '')
        .map((input) => [input.name, input.value]));

    const titleEl = dialog.querySelector('[data-confirm-title]');
    const textEl = dialog.querySelector('[data-confirm-text]');
    const reasonWrap = dialog.querySelector('[data-confirm-reason]');
    const reasonInput = reasonWrap.querySelector('textarea');
    const reasonLabel = reasonWrap.querySelector('label');
    const reasonError = reasonWrap.querySelector('[data-confirm-reason-error]');
    const ok = dialog.querySelector('[data-confirm-ok]');
    const cancel = dialog.querySelector('[data-confirm-cancel]');
    const icon = dialog.querySelector('[data-confirm-icon]');

    titleEl.textContent = title;
    textEl.textContent = text;
    textEl.hidden = text === '';
    ok.textContent = confirmLabel ?? t('confirm');
    ok.classList.toggle('admin-btn-danger', danger);
    ok.classList.toggle('admin-btn-primary', !danger);
    icon.classList.toggle('text-status-danger', danger);
    icon.classList.toggle('bg-status-danger-tint', danger);
    icon.classList.toggle('text-status-warning', !danger);
    icon.classList.toggle('bg-status-warning-tint', !danger);
    reasonWrap.hidden = !reason;
    reasonInput.value = '';
    reasonError.hidden = true;
    reasonInput.removeAttribute('aria-invalid');
    ok.disabled = false;

    const clearFieldErrors = () => {
        fieldsWrap?.querySelectorAll('[data-error-for]').forEach((error) => { error.hidden = true; error.textContent = ''; });
        fieldsWrap?.querySelectorAll('[aria-invalid]').forEach((input) => input.removeAttribute('aria-invalid'));
    };

    const showFieldErrors = (errors) => {
        let unplaced = '';

        Object.entries(errors).forEach(([name, messages]) => {
            const message = [messages].flat()[0] ?? '';
            const slot = fieldsWrap?.querySelector(`[data-error-for="${CSS.escape(name)}"]`);

            if (!slot) {
                unplaced ||= message;

                return;
            }

            slot.textContent = message;
            slot.hidden = false;
            fieldsWrap.querySelector(`[name="${CSS.escape(name)}"]`)?.setAttribute('aria-invalid', 'true');
        });

        fieldsError.textContent = unplaced;
        fieldsError.hidden = unplaced === '';
        fieldsWrap?.querySelector('[aria-invalid]')?.focus();
    };

    if (reason) {
        reasonLabel.textContent = reason.label ?? t('reason');
    }

    return new Promise((resolve) => {
        const finish = (confirmed, result = null) => {
            ok.removeEventListener('click', onOk);
            cancel.removeEventListener('click', onCancel);
            dialog.removeEventListener('cancel', onCancel);
            dialog.close();
            const values = fieldValues();
            fieldsWrap?.replaceChildren();
            ok.disabled = false;
            resolve({ confirmed, reason: reasonInput.value.trim(), values, result });
        };

        const onOk = async () => {
            if (ok.disabled) {
                return;
            }

            clearFieldErrors();
            fieldsError.hidden = true;

            const missing = [...(fieldsWrap?.querySelectorAll('[required]') ?? [])].find((input) => input.value === '');

            if (missing) {
                fieldsError.textContent = t('field_required');
                fieldsError.hidden = false;
                missing.setAttribute('aria-invalid', 'true');
                missing.focus();

                return;
            }

            if (reason && reasonInput.value.trim().length < (reason.min ?? 5)) {
                reasonError.textContent = t('reason_short', { min: reason.min ?? 5 });
                reasonError.hidden = false;
                reasonInput.setAttribute('aria-invalid', 'true');
                reasonInput.focus();

                return;
            }

            if (!submit) {
                finish(true);

                return;
            }

            ok.disabled = true;
            const result = await submit({ ...fieldValues(), ...(reason ? { reason: reasonInput.value.trim() } : {}) });
            ok.disabled = false;

            if (!result.ok && result.status === 422 && result.body?.errors) {
                showFieldErrors(result.body.errors);

                return;
            }

            finish(true, result);
        };

        const onCancel = (event) => {
            event?.preventDefault?.();
            finish(false);
        };

        ok.addEventListener('click', onOk);
        cancel.addEventListener('click', onCancel);
        dialog.addEventListener('cancel', onCancel);
        dialog.showModal();
        ((template ? fieldsWrap.querySelector('[name]') : null) ?? (reason ? reasonInput : cancel)).focus();
    });
}
