/**
 * Admin entry (M14). Same contract as the storefront entry: page data in the nonced JSON block
 * `#store-config`, enhanced elements name their module in `data-module`, modules load on demand
 * (admin modules first, then the shared storefront ones such as `auth-form` and `two-factor`).
 * DataTables is only in the admin table chunk.
 */
const modules = import.meta.glob(['./admin/*.js', './modules/{auth-form,two-factor,popover}.js']);

function loaderFor(name) {
    return modules[`./admin/${name}.js`] ?? modules[`./modules/${name}.js`];
}

function boot() {
    const seen = new Set();

    document.querySelectorAll('[data-module]').forEach((element) => {
        element.dataset.module.split(' ').forEach((name) => {
            const loader = loaderFor(name);

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
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
