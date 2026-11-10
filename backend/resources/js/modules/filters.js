/** Listing filters: submit on change (empty filters left out of the URL), mobile filter sheet. */
export default function filters(form) {
    form.addEventListener('submit', () => {
        form.querySelectorAll('input[name], select[name]').forEach((field) => {
            const unchecked = (field.type === 'radio' || field.type === 'checkbox') && !field.checked;

            if (!unchecked && field.value === '') {
                field.disabled = true;
            }
        });
    });

    form.querySelectorAll('[data-auto-submit]').forEach((control) => {
        control.addEventListener('change', () => form.requestSubmit());
    });

    const toggle = form.querySelector('[data-filter-toggle]');
    const panel = form.querySelector('[data-filter-panel]');

    toggle?.addEventListener('click', () => {
        const open = !panel.hasAttribute('data-open');
        panel.toggleAttribute('data-open', open);
        toggle.setAttribute('aria-expanded', String(open));
    });
}
