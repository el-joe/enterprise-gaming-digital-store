<?php

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Blade;

it('ships every logo, mark and icon file the brand config points to', function (): void {
    $files = collect();

    foreach (['logo', 'mark'] as $variant) {
        foreach (config("brand.$variant.widths") as $width) {
            foreach (['avif', 'webp'] as $extension) {
                $files->push(sprintf(config("brand.$variant.path"), $width).'.'.$extension);
            }
        }
    }

    $files->push(config('brand.email_logo.path'), config('brand.og_image'), 'favicon.ico', 'site.webmanifest')
        ->push('images/brand/favicon-96.png', 'images/brand/icon-180.png', 'images/brand/icon-192.png', 'images/brand/icon-512.png');

    $files->each(fn (string $file) => expect(public_path($file))->toBeFile());
});

it('renders the logo as a picture with avif before webp and fixed dimensions', function (): void {
    $html = Blade::render('<x-brand.logo :height="40" eager />');

    expect($html)
        ->toContain('type="image/avif"')
        ->toContain('logo-640w.avif 640w')
        ->toContain('alt="'.config('brand.name').'"')
        ->toContain('width="178"')
        ->toContain('height="40"')
        ->toContain('fetchpriority="high"')
        ->not->toContain('loading="lazy"')
        ->and(strpos($html, 'image/avif'))->toBeLessThan(strpos($html, 'image/webp'));
});

it('renders the square mark lazily and wraps light surfaces in a dark chip', function (): void {
    $html = Blade::render('<x-brand.logo variant="mark" surface="light" :height="32" />');

    expect($html)
        ->toContain('mark-64.avif 64w')
        ->toContain('width="32"')
        ->toContain('loading="lazy"')
        ->toContain('bg-[#07080B]');
});

it('puts the brand logo and name in transactional email', function (): void {
    $html = (string) (new MailMessage)->line('Your code is ready.')->render();

    expect($html)
        ->toContain(asset(config('brand.email_logo.path')))
        ->toContain(config('brand.name'))
        ->not->toContain('laravel.com/img/notification-logo');
});
