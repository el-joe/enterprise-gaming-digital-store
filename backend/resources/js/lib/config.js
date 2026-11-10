/** Page configuration from the nonced `#store-config` JSON block (layouts/storefront.blade.php). */
let cached = null;

export function config() {
    if (cached === null) {
        const element = document.getElementById('store-config');
        cached = element ? JSON.parse(element.textContent) : { routes: {}, i18n: {} };
    }

    return cached;
}

/** A translated string from `lang/{locale}/storefront.php` `js`, with `:name` replacements. */
export function t(key, replacements = {}) {
    let text = config().i18n?.[key] ?? key;

    Object.entries(replacements).forEach(([name, value]) => {
        text = text.replaceAll(`:${name}`, String(value));
    });

    return text;
}
