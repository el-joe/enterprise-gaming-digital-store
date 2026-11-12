import { request } from '../lib/http.js';
import { busy, clearErrors, showErrors } from '../lib/forms.js';
import { confirmDialog } from './dialog.js';

/**
 * Sandbox / Live segmented control (supplier detail). Each `[data-mode-option]` button PATCHes
 * `{ mode }` to the module's `data-action` after the confirmation dialog (its own title, text and
 * `data-confirm-danger` for production). A refusal (422 `mode`, no credentials for that mode)
 * shows beside the control in `[data-error-for="mode"]`; success reloads the page.
 */
export default function modeSwitch(root) {
    root.addEventListener('click', async (event) => {
        const option = event.target.closest('[data-mode-option]');

        if (!option || !root.contains(option) || option.getAttribute('aria-pressed') === 'true' || option.disabled) {
            return;
        }

        clearErrors(root);

        const { confirmed } = await confirmDialog({
            title: option.dataset.confirmTitle,
            text: option.dataset.confirmText ?? '',
            confirmLabel: option.dataset.confirmLabel,
            danger: option.dataset.confirmDanger !== undefined,
        });

        if (!confirmed) {
            return;
        }

        const result = await busy(option, () => request('PATCH', root.dataset.action, { body: { mode: option.dataset.mode } }));

        if (!result.ok) {
            showErrors(root, result);

            return;
        }

        window.location.reload();
    });
}
