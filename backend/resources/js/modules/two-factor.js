import { del, post } from '../lib/http.js';
import { busy, clearErrors, showErrors } from '../lib/forms.js';

/**
 * Two-factor authentication on the security page (M13, API 4a):
 * enable: current password (`POST /security/two-factor`) -> QR code (an image served by the
 * account page route, so the secret is never in a script) and the setup key -> a code from the
 * app (`POST /security/two-factor/confirm`) -> the recovery codes, shown once;
 * disable: current password (`DELETE /security/two-factor`).
 */
export default function twoFactor(root) {
    const startForm = root.querySelector('[data-2fa-start]');
    const confirmForm = root.querySelector('[data-2fa-confirm]');
    const disableForm = root.querySelector('[data-2fa-disable]');
    const setup = root.querySelector('[data-2fa-setup]');
    const recovery = root.querySelector('[data-2fa-recovery]');

    startForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(startForm);

        const password = startForm.querySelector('[name="current_password"]');
        const result = await busy(startForm.querySelector('[type="submit"]'), () => post(root.dataset.startUrl, { current_password: password.value }));

        if (!result.ok) {
            showErrors(startForm, result);

            return;
        }

        password.value = '';
        const image = setup.querySelector('[data-2fa-qr]');
        image.src = `${root.dataset.qrUrl}?v=${Date.now()}`;
        setup.querySelector('[data-2fa-secret]').textContent = (result.body?.data?.secret ?? '').replace(/(.{4})/g, '$1 ').trim();
        startForm.hidden = true;
        setup.hidden = false;
        confirmForm.querySelector('[name="code"]').focus();
    });

    confirmForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(confirmForm);

        const code = confirmForm.querySelector('[name="code"]');
        const result = await busy(confirmForm.querySelector('[type="submit"]'), () => post(root.dataset.confirmUrl, { code: code.value.replace(/\s+/g, '') }));

        if (!result.ok) {
            if (!result.body?.errors && result.body?.code === 'two_factor_invalid') {
                result.body.errors = { code: [result.body.message] };
            }

            showErrors(confirmForm, result);

            return;
        }

        const list = recovery.querySelector('[data-2fa-codes]');
        list.replaceChildren(...(result.body?.data?.recovery_codes ?? []).map((value) => {
            const item = document.createElement('li');
            item.textContent = value;

            return item;
        }));
        setup.hidden = true;
        recovery.hidden = false;
        recovery.querySelector('[data-2fa-done]').focus();
    });

    recovery?.querySelector('[data-2fa-done]').addEventListener('click', () => window.location.reload());

    disableForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(disableForm);

        const password = disableForm.querySelector('[name="current_password"]');
        const result = await busy(disableForm.querySelector('[type="submit"]'), () => del(root.dataset.startUrl, { body: { current_password: password.value } }));

        if (!result.ok) {
            showErrors(disableForm, result);

            return;
        }

        window.location.reload();
    });
}
