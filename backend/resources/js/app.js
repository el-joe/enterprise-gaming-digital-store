/**
 * Storefront entry (M12). Page data comes from the nonced JSON block `#store-config`; each
 * enhanced element names its module with `data-module`, and modules load on demand. No inline
 * scripts, no framework, and no money arithmetic: every amount shown is a server-formatted
 * string from the JSON envelope or the Blade page.
 */
const modules = import.meta.glob('./modules/*.js');

function boot() {
    const seen = new Set();

    document.querySelectorAll('[data-module]').forEach((element) => {
        element.dataset.module.split(' ').forEach((name) => {
            const loader = modules[`./modules/${name}.js`];

            if (!loader) {
                return;
            }

            loader().then((module) => {
                if (!seen.has(name) && typeof module.once === 'function') {
                    seen.add(name);
                    module.once();
                }

                module.default(element);
            });
        });
    });

    import('./modules/menu.js').then((module) => module.default());
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
