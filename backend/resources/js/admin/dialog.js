import { t } from '../lib/config.js';

/**
 * The confirmation dialog of the admin layout (`[data-confirm-dialog]`, a native <dialog>).
 * Resolves to `{ confirmed, reason }`. With `reason` the dialog asks for a written reason
 * (`min` characters) before the confirm button works; the reason goes to the audit log.
 */
export function confirmDialog({ title, text = '', confirmLabel, danger = false, reason = null }) {
    const dialog = document.querySelector('[data-confirm-dialog]');

    if (!dialog) {
        return Promise.resolve({ confirmed: window.confirm(title), reason: '' });
    }

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

    if (reason) {
        reasonLabel.textContent = reason.label ?? t('reason');
    }

    return new Promise((resolve) => {
        const finish = (confirmed) => {
            ok.removeEventListener('click', onOk);
            cancel.removeEventListener('click', onCancel);
            dialog.removeEventListener('cancel', onCancel);
            dialog.close();
            resolve({ confirmed, reason: reasonInput.value.trim() });
        };

        const onOk = () => {
            if (reason && reasonInput.value.trim().length < (reason.min ?? 5)) {
                reasonError.textContent = t('reason_short', { min: reason.min ?? 5 });
                reasonError.hidden = false;
                reasonInput.setAttribute('aria-invalid', 'true');
                reasonInput.focus();

                return;
            }

            finish(true);
        };

        const onCancel = (event) => {
            event?.preventDefault?.();
            finish(false);
        };

        ok.addEventListener('click', onOk);
        cancel.addEventListener('click', onCancel);
        dialog.addEventListener('cancel', onCancel);
        dialog.showModal();
        (reason ? reasonInput : cancel).focus();
    });
}
