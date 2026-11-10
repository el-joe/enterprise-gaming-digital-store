import { post } from '../lib/http.js';
import { busy, clearErrors, showErrors } from '../lib/forms.js';

/**
 * Guest order access: after the signed email link, the shopper asks for a one-time code
 * (`/api/v1/checkout/guest/otp/send`, same answer whether or not the email matches) and enters
 * it (`/otp/verify`); the session then opens the order page with its codes.
 */
export default function guestAccess(root) {
    const sendForm = root.querySelector('[data-otp-send]');
    const verifyForm = root.querySelector('[data-otp-verify]');
    const order = root.dataset.order;

    sendForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(sendForm);
        const email = sendForm.querySelector('[name="email"]').value.trim();
        const result = await busy(sendForm.querySelector('button[type="submit"]'), () => post(root.dataset.sendUrl, { order_public_id: order, email }));

        if (!result.ok) {
            showErrors(sendForm, result);

            return;
        }

        const message = sendForm.querySelector('[data-send-message]');
        message.textContent = result.body?.message ?? '';
        message.hidden = false;
        verifyForm.querySelector('[name="code"]').focus();
    });

    verifyForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(verifyForm);
        const code = verifyForm.querySelector('[name="code"]').value.trim();
        const result = await busy(verifyForm.querySelector('button[type="submit"]'), () => post(root.dataset.verifyUrl, { order_public_id: order, code }));

        if (!result.ok) {
            if (result.body?.code === 'otp_invalid' && !result.body.errors) {
                result.body.errors = { code: [result.body.message] };
            }

            showErrors(verifyForm, result);

            return;
        }

        window.location.reload();
    });
}
