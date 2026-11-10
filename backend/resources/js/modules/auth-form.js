import { post } from '../lib/http.js';
import { busy, clearErrors, formValues, showErrors } from '../lib/forms.js';

/**
 * Sign-in, registration, two-factor, forgotten and reset password, and verification resend: each
 * form posts its fields to the existing session route (`data-action`) and shows the envelope's
 * field errors beside the fields. Success redirects (`data-redirect`) or shows the message.
 */
export default function authForm(form) {
    const kind = form.dataset.kind;

    form.querySelector('[data-toggle-recovery]')?.addEventListener('click', () => {
        const code = form.querySelector('[data-code-field]');
        const recovery = form.querySelector('[data-recovery-field]');
        code.hidden = !code.hidden;
        recovery.hidden = !recovery.hidden;
        (code.hidden ? recovery : code).querySelector('input').focus();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(form);

        const values = formValues(form);
        const body = { ...values };

        if (kind === 'login') {
            body.remember = values.remember === true;
        }

        if (kind === 'two-factor') {
            const recovery = !form.querySelector('[data-recovery-field]').hidden;
            delete body[recovery ? 'code' : 'recovery_code'];
        }

        const result = await busy(form.querySelector('button[type="submit"]'), () => post(form.dataset.action, body));

        if (!result.ok) {
            const codeError = ['two_factor_invalid', 'two_factor_expired'].includes(result.body?.code);

            if (codeError && !result.body.errors) {
                result.body.errors = { code: [result.body.message] };
            }

            showErrors(form, result);

            return;
        }

        const data = result.body?.data ?? {};

        if (kind === 'login' && data.two_factor_required) {
            window.location.assign(form.dataset.twoFactorUrl);

            return;
        }

        if (kind === 'message') {
            const success = form.querySelector('[data-form-success]');
            success.querySelector('[data-form-success-text]').textContent = result.body?.message ?? '';
            success.hidden = false;

            return;
        }

        window.location.assign(form.dataset.redirect || '/');
    });
}
