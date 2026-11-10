# Design Tokens (M1)

Figma file: https://www.figma.com/design/JkUUSR3WGBYC9ewy2qRFQO

Source of truth is the Figma file: variable collections `Primitives`, `Theme` (modes `Dark`, `Admin Light`), `Layout`; text and effect styles. Names below map 1:1 to Tailwind 4 `@theme` keys (`color/surface/raised` is `--color-surface-raised`, utility `bg-surface-raised`). Brand: boxcards.shop (replaces the working name PLAYVAULT).

Logo rules: Figma components `Logo / boxcards.shop` (full logo, box + wordmark; Size S 28 / M 36 / L 64 px high) and `Logo / boxcards.shop mark` (box only; S 24 / M 32 / L 64). Both use the supplied raster masters (`backend/resources/assets/images/brand/logo-master.png` 1885x424, `mark-master.png` 538x538). Keep the aspect ratio and never recolour, stretch or redraw. Use the full logo in headers and footers; use the mark in the collapsed admin rail, favicon and app icon. On light or white surfaces (Admin Light, emails) place the logo on an ink-950 `#07080B` chip with the key-cut corner (`Tone=Chip` / `Surface=Light` variants), because the white "box" text and volt green fail contrast on light backgrounds. The logo green matches volt-500, so colour tokens are unchanged. The logo artwork contains third-party platform symbols; trademark clearance is pending with the owner.

## Fonts (Google Fonts, Latin + Arabic where noted)

| Role | Family | Weights used | Scripts |
|---|---|---|---|
| Display, prices | Changa | 500 600 700 800 | Latin, Arabic |
| Body, UI, labels | Readex Pro | 400 500 600 | Latin, Arabic |
| Codes, SKUs, data | IBM Plex Mono | 400 500 | Latin |

Arabic rules: no letter spacing, no uppercase, line height about +15% from 20px up.

## Core colours

Ink (surfaces and text): 950 `#07080B`, 900 `#0C0E13`, 850 `#12151C`, 800 `#191D26`, 700 `#252A35`, 600 `#383F4D`, 500 `#5A6374`, 400 `#8791A3`, 300 `#B3BBC9`, 200 `#D7DBE3`, 100 `#ECEEF2`, 50 `#F6F7F9`.

Brand: volt-500 `#C8FF2E` (primary CTA), volt-300 `#E4FF85`, volt-600 `#A6DB10`, volt-800 `#4A6404`, volt-900 `#222E05`; ember-500 `#FF5A2B`, ember-400 `#FF7A52`, ember-900 `#3B1408`; ultra-400 `#7C7EFF`, ultra-500 `#5A50FF`, ultra-900 `#1B1754`; aqua-500 `#12C4AB`; flux-500 `#EB2FA8`; sun-500 `#F5A800`.

Status: green-400 `#4EE39B`, sun-400 `#FFC53D`, red-400 `#FF6B7A`, red-500 `#F23D52`, blue-400 `#5CB2FF`.

## Semantic tokens (Dark / Admin Light)

| Token | Dark | Admin Light |
|---|---|---|
| `surface-canvas` | ink-950 | ink-50 |
| `surface-base` | ink-900 | white |
| `surface-raised` | ink-850 | white |
| `surface-overlay` | ink-800 | white |
| `surface-sunken` | ink-950 | ink-100 |
| `line-subtle` / `line-default` / `line-strong` | ink-800 / 700 / 600 | ink-100 / 200 / 300 |
| `content-primary` / `secondary` / `muted` | ink-50 / 300 / 400 | ink-900 / 600 / 500 |
| `content-on-accent` | ink-950 | ink-950 |
| `accent-primary` / `-hover` / `-tint` / `-text` | volt-500 / 300 / 900 / 500 | volt-500 / 600 / 300 / 800 |
| `accent-ember` / `-tint` / `-text` | ember-500 / 900 / 400 | ember-500 / red-100 / ember-600 |
| `accent-ultra` / `focus-ring` | ultra-400 | ultra-500 |
| `status-success` `-warning` `-danger` `-info` | green-400, sun-400, red-400, blue-400 | -700 steps |
| `*-tint` for status | `-900` steps | `-100` steps (warning: sun-400) |
| `field-*` (platform tiles) | volt/ember/ultra/aqua/flux/sun-900, ink-800 | same |

## Spacing and radius

Spacing base 4px (`--spacing: 0.25rem`). Scale used: 0.5, 1, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10, 12, 16, 20, 24.

Radius: `xs` 4, `sm` 6, `md` 10, `lg` 14, `xl` 20, `key` 28, `full`.

Key-cut silhouette (brand signature, mirrors automatically): `rounded-ss-sm rounded-se-[28px] rounded-ee-sm rounded-es-sm`. Buttons: `rounded-ss-sm rounded-se-[14px] rounded-ee-sm rounded-es-sm`.

## Type scale (font-size/line-height px)

display-hero 72/76, display-xl 56/60, display-lg 40/46, display-md 32/38, display-sm 26/32 (Changa); heading-lg 22/30, heading-md 18/26, heading-sm 16/24; body-lg 16/26, body-md 14/22, body-sm 13/20, body-caption 12/16; label-lg 16/24, label-md 14/20, label-sm 12/16 (Readex Pro); overline 12/16 Changa +12% uppercase; price-xl 44/48, price-lg 28/32, price-md 20/26, price-sm 16/20 (Changa); mono-lg 20/28, mono-md 13/20, mono-sm 12/16.

## Effects

shadow-card `0 8px 24px rgb(0 0 0/.35)`, shadow-raised `0 16px 40px rgb(0 0 0/.45)`, shadow-overlay `0 32px 80px rgb(0 0 0/.6)`, glow-volt `0 0 28px rgb(200 255 46/.28)`, glow-ember `0 0 28px rgb(255 90 43/.3)`.

## Layout

Breakpoints: sm 375, md 768, lg 1280 (design canvas 1440). Margins 16 / 32 / 80; grid 4 / 8 / 12 columns; gutters 12 / 16 / 24; content max width 1280.

## Implementation notes

RTL: `dir="rtl"`, logical utilities only; do not mirror logos, currency values, SKUs, digital codes (full rules on the Foundations page). Motion: fast 120ms, base 200ms, slow 360ms, `cubic-bezier(.2,.8,.2,1)`, honour `prefers-reduced-motion`. Chart.js defaults: series order volt, ultra, ember, aqua; grid ink-700; 2px lines; always a legend at 2+ series; one y axis. Palette validator: CVD and contrast pass on `#12151C`; bright volt and aqua fall outside the suggested lightness band, a deliberate brand decision offset by legends and labels.

BD8: platforms use a plain-text tile and an empty `Logo Slot` until rights are confirmed. Reviews in frames are flagged "Sample content".

## Phase 2 screens

Added to the Figma file on the existing pages, in a grid of rows (`Row / Phase 2 — …` headings, 1560 px column pitch). All frames reuse the library components and bind to `Theme`/`Layout` variables and the text styles. Admin frames clone the approved admin shell (sidebar + top bar); account frames clone the approved account shell. Mobile account frames are 390 wide as briefed (the Phase 1 mobile frames are 375).

| Page | Frame | Node ID |
|---|---|---|
| Admin | Admin / Customers / List | 30:5569 |
| Admin | Admin / Customers / List — Loading (skeleton) | 32:6521 |
| Admin | Admin / Customers / Detail | 31:5980 |
| Admin | Admin / Customers / Detail — Block confirm (Alert Dialog, Error) | 32:6259 |
| Admin | Admin / Marketing / Coupons | 33:6812 |
| Admin | Admin / Marketing / Coupon Edit — Validation error | 33:7565 |
| Admin | Admin / Marketing / Affiliates | 34:7346 |
| Admin | Admin / Marketing / Affiliate Detail | 34:8035 |
| Admin | Admin / Content / Pages | 35:7897 |
| Admin | Admin / Content / Page Editor (EN/AR, SEO) | 35:8621 |
| Admin | Admin / Content / Homepage Banners | 37:8407 |
| Admin | Admin / Content / FAQ | 37:9095 |
| Admin | Admin / Support / Tickets | 41:8775 |
| Admin | Admin / Support / Ticket Thread | 41:9540 |
| Admin | Admin / Reviews / Moderation Queue | 42:9280 |
| Admin | Admin / Reviews / Moderation Queue — Empty | 42:10182 |
| Account | Account / Profile & Security — Desktop 1440 / Mobile 390 | 44:2703 / 44:3164 |
| Account | Account / My Reviews — Desktop 1440 / Mobile 390 | 45:3130 / 45:3648 |
| Account | Account / Notifications — Desktop 1440 / Mobile 390 | 46:3573 / 46:4018 |
| Account | Account / Digital Codes — Desktop 1440 / Mobile 390 | 47:3962 / 47:4439 |
| RTL | Account / Digital Codes — Mobile 390 (AR, RTL), key-cut corner mirrored | 48:3306 |

Library additions: `Logo / boxcards.shop mark` (38:10186, Size S/M/L × Surface Dark/Light); `Logo / boxcards.shop` (4:388) rebuilt from the supplied masters with `Tone=Volt|Chip` (Chip replaces the old Mono tone). The admin rail (tablet) now starts with the mark. Admin sidebars in new frames add a `Reviews` nav item; account navs add `My reviews` and `Notifications` and rename `Security` to `Profile & security`.

Derived or not-yet-stored data shown in Phase 2 frames: ticket SLA (computed from priority, created_at and status; no column), canned replies (no table yet), review reject/hide reason (written to `audit_logs.reason`; hide = soft delete), phone verification (no column; the UI says it is not available).
