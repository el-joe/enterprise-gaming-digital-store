/**
 * Admin modal dialogs (supplier edit, credentials, create): a `[data-modal-open="id"]` button
 * inside the module element opens the native <dialog id> with showModal(); `[data-modal-close]`
 * closes its dialog. The forms inside are ordinary `form` modules (errors beside the fields).
 * A `#id` URL hash naming a dialog this module opens (the list's Edit link) opens it on load.
 */
export default function adminModal(root) {
    const target = window.location.hash.slice(1);

    if (target !== '' && root.querySelector(`[data-modal-open="${CSS.escape(target)}"]`)) {
        const dialog = document.getElementById(target);

        if (dialog instanceof HTMLDialogElement && !dialog.open) {
            dialog.showModal();
        }
    }

    root.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-modal-open]');

        if (opener && root.contains(opener)) {
            const dialog = document.getElementById(opener.dataset.modalOpen);

            if (dialog instanceof HTMLDialogElement) {
                event.preventDefault();
                dialog.showModal();
                dialog.querySelector('input:not([type="hidden"]), select, textarea')?.focus();
            }

            return;
        }

        const closer = event.target.closest('[data-modal-close]');

        if (closer && root.contains(closer)) {
            event.preventDefault();
            closer.closest('dialog')?.close();
        }
    });
}
