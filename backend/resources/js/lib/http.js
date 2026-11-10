import { config } from './config.js';

/**
 * JSON call to the store's own endpoints with the session cookie, CSRF token and page language.
 * Resolves to `{ ok, status, body }` where `body` is the standard envelope (or null); never throws
 * for HTTP errors, only for network failures, which resolve to `{ ok: false, status: 0 }`.
 */
export async function request(method, url, { body, headers = {}, signal } = {}) {
    try {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            signal,
            headers: {
                Accept: 'application/json',
                'Accept-Language': config().locale,
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': config().csrf,
                ...(body !== undefined ? { 'Content-Type': 'application/json' } : {}),
                ...headers,
            },
            body: body !== undefined ? JSON.stringify(body) : undefined,
        });

        let payload = null;

        try {
            payload = await response.json();
        } catch {
            payload = null;
        }

        return { ok: response.ok, status: response.status, body: payload };
    } catch (error) {
        if (error?.name === 'AbortError') {
            throw error;
        }

        return { ok: false, status: 0, body: null };
    }
}

export const get = (url, options) => request('GET', url, options);
export const post = (url, body, options = {}) => request('POST', url, { ...options, body: body ?? {} });
export const patch = (url, body, options = {}) => request('PATCH', url, { ...options, body });
export const del = (url, options) => request('DELETE', url, options);

/** A server page fragment (Blade `@fragment`), as HTML text, or null. */
export async function fragment(url, name) {
    try {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: { Accept: 'text/html', 'X-Fragment': name, 'X-Requested-With': 'XMLHttpRequest' },
        });

        return response.ok ? await response.text() : null;
    } catch {
        return null;
    }
}
