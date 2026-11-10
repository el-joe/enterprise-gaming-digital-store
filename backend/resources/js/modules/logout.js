import { config } from '../lib/config.js';
import { post } from '../lib/http.js';

/** Sign out (`POST /logout`, CSRF) and go home. */
export default function logout(button) {
    button.addEventListener('click', async () => {
        button.disabled = true;
        await post(config().routes.logout, {});
        window.location.assign(config().routes.home ?? '/');
    });
}
