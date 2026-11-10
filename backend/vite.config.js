import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { google } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import { readdirSync } from 'node:fs';

/**
 * Raster art referenced from Blade with Vite::asset (docs/ASSETS.md): hero, category, platform
 * and promo banners. Listed as build inputs so every file gets a manifest entry.
 */
const images = readdirSync('resources/assets/images', { recursive: true })
    .filter((file) => /\.(webp|avif)$/.test(file) && !file.startsWith('social'))
    .map((file) => `resources/assets/images/${file}`);

/**
 * Fonts (DESIGN_TOKENS.md): downloaded at build time and served from /build, so the storefront
 * loads no third-party stylesheet and the @fonts style block carries the CSP nonce.
 */
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', ...images],
            refresh: true,
            fonts: [
                google('Changa', {
                    weights: [500, 600, 700, 800],
                    subsets: ['latin', 'arabic'],
                    preload: [{ weight: 700 }],
                }),
                google('Readex Pro', {
                    weights: [400, 500, 600],
                    subsets: ['latin', 'arabic'],
                    preload: [{ weight: 400 }],
                }),
                google('IBM Plex Mono', {
                    weights: [400, 500],
                    subsets: ['latin'],
                    preload: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
