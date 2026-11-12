import { config, t } from '../lib/config.js';

const TYPES = ['image/png', 'image/jpeg', 'image/webp', 'image/avif'];

/**
 * Admin image field (`<x-admin.image-upload>`): pick or drop a file, upload it to
 * `data-upload-url` (multipart, CSRF header), then put the returned media id in the hidden input
 * and show the preview. Remove clears the id (the form sends null). Type and size are checked
 * here first for a quick message; the server checks again and re-encodes. No inline script or
 * style, so it runs under the admin CSP.
 */
export default function imageUpload(root) {
    const file = root.querySelector('[data-image-file]');
    const value = root.querySelector('[data-image-value]');
    const preview = root.querySelector('[data-image-preview]');
    const empty = root.querySelector('[data-image-empty]');
    const busy = root.querySelector('[data-image-busy]');
    const error = root.querySelector('[data-image-error]');
    const remove = root.querySelector('[data-image-remove]');
    const drop = root.querySelector('[data-image-drop]');
    const maxBytes = Number(root.dataset.maxBytes || 0);

    if (!file || !value) {
        return;
    }

    const showError = (message) => {
        error.textContent = message ?? '';
        error.hidden = !message;
        file.toggleAttribute('aria-invalid', Boolean(message));
    };

    const show = (url) => {
        preview.hidden = !url;
        preview.src = url ?? '';
        empty.hidden = Boolean(url);
        remove.hidden = !url;
        root.querySelector('[data-image-choose]').hidden = Boolean(url);
        root.querySelector('[data-image-replace]').hidden = !url;
    };

    const send = async (picked) => {
        showError(null);

        if (!picked) {
            return;
        }

        if (!TYPES.includes(picked.type)) {
            showError(t('image_type'));

            return;
        }

        if (maxBytes > 0 && picked.size > maxBytes) {
            showError(t('image_too_large', { mb: Math.round(maxBytes / 1048576) }));

            return;
        }

        const data = new FormData();
        data.append('file', picked);
        data.append('folder', 'admin-forms');
        busy.hidden = false;

        try {
            const response = await fetch(root.dataset.uploadUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'Accept-Language': config().locale, 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': config().csrf },
                body: data,
            });
            const payload = await response.json().catch(() => null);

            if (!response.ok) {
                const errors = payload?.errors ?? {};
                showError((errors.file ?? [])[0] ?? payload?.message ?? t('image_failed'));

                return;
            }

            value.value = String(payload.data.id);
            show(payload.data.url);
        } catch {
            showError(t('image_failed'));
        } finally {
            busy.hidden = true;
            file.value = '';
        }
    };

    file.addEventListener('change', () => send(file.files?.[0]));

    remove?.addEventListener('click', () => {
        value.value = '';
        show(null);
        showError(null);
    });

    if (drop && !file.disabled) {
        ['dragenter', 'dragover'].forEach((type) => drop.addEventListener(type, (event) => {
            event.preventDefault();
            drop.dataset.dragging = '';
        }));
        ['dragleave', 'drop'].forEach((type) => drop.addEventListener(type, () => delete drop.dataset.dragging));
        drop.addEventListener('drop', (event) => {
            event.preventDefault();
            send(event.dataTransfer?.files?.[0]);
        });
    }
}
